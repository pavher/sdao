<?php
declare(strict_types=1);

/**
 * Created by Vitezslav Zak
 * Date: 15.11.2024
 */

namespace Pavher\Sdao\Utils;


use Nette\StaticClass;

class DBUtil
{
    use StaticClass;

    /**
     * This function extracts column aliases and maps them directly to their
     * corresponding column names with table prefixes.
     *
     * The method simply extracts the column names and aliases from the SQL query,
     * considering only the columns in the SELECT clause,
     * and creates a mapping between the alias and the full column name.
     *
     * @param string $sql The SQL query containing columns with or without table aliases.
     * @return array An associative array where the keys are aliases and the values are
     *               the full column names with their table prefixes (if applicable).
     */
    public static function extractColumnAliasesToColumnsWithPrefixMapping(string $sql): array {
        $aliasMap = [];  // Initialize the array that will store alias-to-column mappings.

        // Regular expression pattern to find columns followed by their aliases (e.g., "column_name AS alias").
        // This pattern ensures that the column name does NOT appear before "FROM" or "JOIN".
        $pattern = '/(?<=SELECT|,)\s+([a-zA-Z0-9_.]+)\s+AS\s+([a-zA-Z0-9_]+)/i';

        // Perform the regex search to find all matches in the SQL query.
        preg_match_all($pattern, $sql, $matches, PREG_SET_ORDER);

        // Iterate over each match to process column and alias
        foreach ($matches as $match) {
            $columnName = $match[1];  // The original column name (before the alias).
            $alias = $match[2];       // The alias given to the column.

            // Check if the column name contains a dot, which indicates a table alias is present.
            $aliasMap[$alias] = $columnName;
        }

        return $aliasMap;  // Return the map of aliases to column names.
    }

    /**
     * Replaces column aliases in an array with their original column names, including table prefixes.
     *
     * This function iterates over an array and, for each key (representing a column alias), checks if the key starts with any
     * alias defined in the `$aliasMap`. If a match is found, the alias is replaced with the corresponding column name
     * from the `$aliasMap`. The function ensures that only valid aliases are replaced, and the rest of the array remains unchanged.
     *
     * Example:
     * Given the following input:
     *
     *     $where = [
     *         'sub_tcomponent_data_visible' => 1,
     *         'sub_tcomponent_data_title' => 'Title',
     *     ];
     *
     *     $aliasMap = [
     *         'sub_tcomponent_data_visible' => 'std.visible',
     *         'sub_tcomponent_data_title' => 'std.title',
     *     ];
     *
     * The method would return:
     *
     *     [
     *         'std.visible' => 1,
     *         'std.title' => 'Title',
     *     ];
     *
     * This is useful when replacing column aliases with full column names with their corresponding table prefixes
     * after processing the query results.
     *
     * @param array $where The input data array where keys represent column aliases.
     * @param array $aliasMap A mapping of aliases to their full column names with table prefixes.
     * @return array The updated array with aliases replaced by full column names.
     */
    public static function replaceAliasesWithOriginalColumnNamesWithPrefixes(array $where, array $aliasMap): array {
        // Initialize the result array where the updated data will be stored
        $result = [];

        // Iterate over each key-value pair in the input data array
        foreach ($where as $key => $value) {
            // For each key (alias), check if it matches any alias in the alias map
            foreach ($aliasMap as $alias => $prefixedName) {
                // If the key starts with the alias, replace it with the full column name (with prefix)
                if (str_starts_with($key, $alias)) {
                    // Replace the alias with the prefixed column name
                    $newKey = preg_replace('/^' . preg_quote($alias, '/') . '/', $prefixedName, $key);
                    // Add the updated key-value pair to the result array
                    $result[$newKey] = $value;
                    // Skip to the next key-value pair in the data
                    continue 2;
                }
            }

            // If no alias match is found, just add the original key-value pair to the result
            $result[$key] = $value;
        }

        // Return the array with aliases replaced by full column names
        return $result;
    }

    /**
     * Replaces column aliases in WHERE conditions using mappings extracted from the provided SQL query.
     *
     * This method extracts the alias mappings from the given SQL query and then uses them to replace
     * the column aliases in the provided WHERE conditions array with the corresponding full column names, including table prefixes.
     * It internally calls `extractColumnAliasesToColumnsWithPrefixMapping` to extract the alias mappings
     * from the SQL query and `replaceAliasesWithOriginalColumnNamesWithPrefixes` to apply those mappings to the WHERE conditions.
     *
     * @param array $where The input WHERE conditions array where keys represent column aliases.
     * @param string $sql The SQL query containing column aliases to be mapped to full column names.
     * @return array The updated WHERE conditions array with aliases replaced by full column names.
     */
    public static function replaceAliasesInWhereMappingsExtractedFromSqlQuery(array $where, string $sql): array {
        // Step 1: Extract alias mappings from the SQL query
        $aliasMap = self::extractColumnAliasesToColumnsWithPrefixMapping($sql);

        // Step 2: Replace the aliases in the WHERE conditions using the alias map
        return self::replaceAliasesWithOriginalColumnNamesWithPrefixes($where, $aliasMap);
    }
}
