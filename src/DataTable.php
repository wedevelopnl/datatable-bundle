<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmen\DataTableBundle\Column\ColumnTypeInterface;
use Webmen\DataTableBundle\Modal\ModalTypeInterface;

final class DataTable
{
    public const ORDER_ASC = 'ASC';
    public const ORDER_DESC = 'DESC';

    private string $name;
    /** @var array<string,ColumnTypeInterface> */
    private array $columns;
    /** @var array<string,ModalTypeInterface> */
    private array $modals;
    private QueryBuilder $queryBuilder;
    private ?string $sortColumn;
    private ?string $sortDirection;

    private Response $response;
    private TranslatorInterface $translator;

    /**
     * @param array<string,ColumnTypeInterface> $columns
     * @param array<string, ModalTypeInterface> $modals
     */
    public function __construct(
        string $name,
        array $columns,
        array $modals,
        QueryBuilder $queryBuilder,
        ?string $sortColumn,
        ?string $sortDirection,
        TranslatorInterface $translator
    ) {
        $this->name = $name;
        $this->columns = $columns;
        $this->modals = $modals;
        $this->queryBuilder = $queryBuilder;
        $this->sortColumn = $sortColumn;
        $this->sortDirection = $sortDirection;
        $this->translator = $translator;
    }

    /**
     * @return array<string, array|string|bool|int|null>
     */
    public function getFrontendConfiguration(string $apiUrl = ''): array
    {
        $columns = [];
        foreach ($this->columns as $column) {
            $columns[] = $column->getFrontendConfiguration($this->translator);
        }

        $modals = [];
        foreach ($this->modals as $modal) {
            $modals[] = $modal->getFrontendConfiguration($this->translator);
        }

        return [
            'name' => $this->name,
            'noResultsText' => $this->translator->trans('datatable.no_results'),
            'resultsPerPageText' => $this->translator->trans('datatable.results_per_page'),
            'apiUrl' => $apiUrl,
            'columns' => $columns,
            'modals' => $modals,
        ];
    }

    public function handleRequest(Request $request): void
    {
        if (!in_array('application/json', $request->getAcceptableContentTypes(), true)) {
            return;
        }

        if ($request->query->get('dataTable') !== $this->name) {
            return;
        }

        $queryBuilder = $this->queryBuilder;

        $filter = [];

        if ($request->query->has('filter')) {
            $filter = array_filter(json_decode($request->query->get('filter'), true), function($value) {
                return $value !== null;
            });
        }

        foreach ($filter as $key => $value) {
            $column = $this->columns[$key];
            if (!$column->isFilterable()) {
                continue;
            }

            $filter = $column->getFilter();
            $filter->apply($queryBuilder, $value);
        }

        $total = (clone $queryBuilder)->select('count(1)')->getQuery()->getSingleScalarResult();

        $sortBy = $request->query->get('sortBy');
        if (strlen($sortBy) > 0) {
            $queryBuilder->orderBy($this->columns[$sortBy]->getPropertyPath(), $request->query->get('sortDirection', 'ASC'));
        } elseif ($this->sortColumn !== null) {
            $queryBuilder->orderBy($this->columns[$this->sortColumn]->getPropertyPath(), $this->sortDirection ?? 'ASC');
        }

        $pageSize = (int)$request->query->get('size');
        $page = (int)$request->query->get('page');

        $queryBuilder->setMaxResults($pageSize);
        $queryBuilder->setFirstResult(($page - 1) * $pageSize);

        $rows = $queryBuilder->getQuery()->getResult();

        $responseBody = ['total' => $total, 'rows' => []];

        foreach ($rows as $row) {
            $responseBody['rows'][] = $this->serialize($row);
        }

        $this->response = new JsonResponse($responseBody);
    }

    public function hasResponse(): bool
    {
        return isset($this->response);
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @return array<string, array|string|bool|int|null>
     */
    private function serialize(object $row): array
    {
        $data = [];
        foreach ($this->columns as $column => $columnType) {
            $data[$column] = $columnType->getValue($row);
        }

        foreach ($this->modals as $modal => $modalType) {
            $data['modal.' . $modal] = $modalType->render($row);
        }

        return $data;
    }
}
