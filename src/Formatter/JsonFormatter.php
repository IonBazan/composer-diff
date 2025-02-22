<?php

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;

class JsonFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    public function render(DiffEntries $prodEntries, DiffEntries $devEntries, $withUrls, $withLicenses)
    {
        $this->format(array(
            'packages' => $this->transformEntries($prodEntries, $withUrls, $withLicenses),
            'packages-dev' => $this->transformEntries($devEntries, $withUrls, $withLicenses),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function renderSingle(DiffEntries $entries, $title, $withUrls, $withLicenses)
    {
        $this->format($this->transformEntries($entries, $withUrls, $withLicenses));
    }

    /**
     * @param array<string, array<string, string|null>>|array<string, array<array<string, string|null>>> $data
     *
     * @return void
     */
    private function format(array $data)
    {
        // @phpstan-ignore argument.type
        $this->output->writeln(json_encode($data, 128)); // JSON_PRETTY_PRINT
    }

    /**
     * @param bool $withUrls
     * @param bool $withLicenses
     *
     * @return array<array<string, mixed>>
     */
    private function transformEntries(DiffEntries $entries, $withUrls, $withLicenses)
    {
        $rows = array();

        foreach ($entries as $entry) {
            $row = $this->transformEntry($entry);

            if ($withUrls) {
                $row['compare'] = $this->getUrl($entry);
                $row['link'] = $this->getProjectUrl($entry->getOperation());
            }

            if ($withLicenses) {
                $row['license'] = $this->getLicenses($entry);
            }

            $rows[$row['name']] = $row;
        }

        return $rows;
    }

    /**
     * @return array<string, string|bool|null>
     */
    private function transformEntry(DiffEntry $entry)
    {
        $operation = $entry->getOperation();

        if ($operation instanceof InstallOperation) {
            return array(
                'name' => $operation->getPackage()->getName(),
                'direct' => $entry->isDirect(),
                'operation' => $entry->getType(),
                'version_base' => null,
                'version_target' => $operation->getPackage()->getFullPrettyVersion(),
            );
        }

        if ($operation instanceof UpdateOperation) {
            return array(
                'name' => $operation->getInitialPackage()->getName(),
                'direct' => $entry->isDirect(),
                'operation' => $entry->getType(),
                'version_base' => $operation->getInitialPackage()->getFullPrettyVersion(),
                'version_target' => $operation->getTargetPackage()->getFullPrettyVersion(),
            );
        }

        if ($operation instanceof UninstallOperation) {
            return array(
                'name' => $operation->getPackage()->getName(),
                'direct' => $entry->isDirect(),
                'operation' => $entry->getType(),
                'version_base' => $operation->getPackage()->getFullPrettyVersion(),
                'version_target' => null,
            );
        }

        throw new \InvalidArgumentException('Invalid operation');
    }
}
