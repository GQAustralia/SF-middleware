<?php

namespace App\Resolvers;

trait InsertIgnoreBulkMySqlResolver
{
    /**
     * @param $table
     * @param array $insertFields
     * @param array $valueCollection
     * @return string
     */
    public function resolve($table, array $insertFields, array $valueCollection)
    {
        $query = "INSERT IGNORE INTO {$table} ({$this->buildInsertFields($insertFields)}) VALUES ";
        $query .= $this->buildFieldValuePlaceHolderString($valueCollection);

        return $query;
    }

    /**
     * @param array $fields
     * @return string
     */
    private function buildInsertFields(array $fields)
    {
        asort($fields);
        return implode(',', $fields);
    }

    /**
     * @param array $valueCollection
     * @return string
     */
    private function buildFieldValuePlaceHolderString(array $valueCollection)
    {
        $placeholder = '';

        foreach ($valueCollection as $attributes) {
            ksort($attributes);
            $placeholder .= '("' . implode('","', $attributes) . '"),';
        }

        return rtrim($placeholder, ',');
    }
}
