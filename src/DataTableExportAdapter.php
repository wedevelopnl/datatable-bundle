<?php

declare(strict_types=1);

namespace Webmen\DataTableBundle;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\Common\Creator\WriterEntityFactory;
use Doctrine\ORM\QueryBuilder;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmen\DataTableBundle\Column\ColumnTypeInterface;

final class DataTableExportAdapter implements DataTableExportInterface
{
    private TranslatorInterface $translator;

    private Security $security;

    private string $exportDatatableDirectory;

    private ?string $exportRight;

    /** @var array<string, ColumnTypeInterface> */
    private array $columns;

    public function __construct(
        TranslatorInterface $translator,
        Security $security,
        string $exportDatatableDirectory
    ) {
        $this->translator = $translator;
        $this->security = $security;
        $this->exportDatatableDirectory = $exportDatatableDirectory;
    }

    public function canExport(): bool
    {
        return $this->exportRight !== null && $this->security->isGranted($this->exportRight);
    }

    /**
     * @param array<string, ColumnTypeInterface> $columns
     */
    public function export(QueryBuilder $queryBuilder, Request $request, string $dataTableName, array $columns): string
    {
        $visibleColumns = array_filter(json_decode($request->query->get('columns'), true));
        $this->setColumns($columns, $visibleColumns);

        $filename = sprintf('%s-%s', (new \DateTime())->format('YmdHis'), $dataTableName);
        $fullName = sprintf('%s/%s.xlsx', $this->exportDatatableDirectory, $filename);

        $writer = new Writer();
        $writer->openToFile($fullName);

        $singleRow = Row::fromValues($this->getHeaders());
        $writer->addRow($singleRow);

        foreach ($queryBuilder->getQuery()->toIterable() as $row) {
            $singleRow = Row::fromValues($this->serializeExport($row));
            $writer->addRow($singleRow);
        }
        $writer->close();

        return $fullName;
    }

    public function setExportRight(string $exportRight): void
    {
        $this->exportRight = $exportRight;
    }

    /**
     * @param array<string, ColumnTypeInterface> $columns
     * @param array<string, bool>                $visibleColumns
     */
    private function setColumns(array $columns, array $visibleColumns): void
    {
        foreach ($columns as $column => $columnType) {
            if (!isset($visibleColumns[$column]) || !$visibleColumns[$column] || !$columnType->isExportable()) {
                continue;
            }

            $this->columns[$column] = $columnType;
        }
    }

    /**
     * @return array<int, string>
     */
    private function getHeaders(): array
    {
        $headers = [];
        foreach ($this->columns as $column) {
            $headers[] = $this->translator->trans($column->getLabel());
        }

        return $headers;
    }

    /**
     * @return array<int, array|string|bool|int|null>
     */
    private function serializeExport(object $row): array
    {
        $data = [];
        foreach ($this->columns as $columnType) {
            $value = $columnType->getExportValue($row);
            $data[] = $value;
        }

        return $data;
    }
}
