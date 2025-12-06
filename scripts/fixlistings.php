<?php
// This file is part of moodle-ws-catalog.
//
// moodle-ws-catalog is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// moodle-ws-catalog is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with moodle-ws-catalog. If not, see <https://www.gnu.org/licenses/>.

/**
 * Fixes listings of core/plugin versions, list of plugins and function names that do not start with the plugin name.
 * Removes version files that are identical to the previous version.
 */

$maindir = dirname(__DIR__);
$coredir = $maindir . '/core';
$pluginsdir = $maindir . '/plugins';
$pluginslistfile = $maindir . '/pluginslist.txt';
$lines = [];

if (!is_dir($coredir) || !is_dir($pluginsdir) || !file_exists($pluginslistfile)) {
    echo "Can not find the json files location.\n";
    exit(1);
}

// Make sure we do not delete plugins that were not yet analysed.
$pluginlines = preg_split('/\n/', file_get_contents($pluginslistfile), -1, PREG_SPLIT_NO_EMPTY);
$knownplugins = array_map(function($line) {
    return explode(':', $line)[0];
}, $pluginlines);
$knownplugins = array_diff($knownplugins, ['core']);

// Scan all versions in core/ directory.
$coreinfo = organise_dir($coredir, function($name) {
    return strpos($name, 'core_') !== 0;
});
$lines[] = 'core' . $coreinfo;

// Scan directory plugins/ and find all subdirectories, for each plugin find versions and odd function names.
$plugins = array_filter(scandir($pluginsdir), function ($item) use ($pluginsdir) {
    return is_dir($pluginsdir . '/' . $item) && !in_array($item, ['.', '..']);
});
$plugins = array_unique(array_merge($plugins, $knownplugins));
sort($plugins);
foreach ($plugins as $plugin) {
    $plugininfo = organise_dir($pluginsdir . '/' . $plugin, function($name) use ($plugin, $plugins) {
        if (strpos($name, $plugin . '_') !== 0 || strpos($name, 'core_') === 0) return true;
        foreach (array_diff($plugins, [$plugin]) as $otherplugin) {
            if (strpos($name, $otherplugin . '_') === 0) {
                return true;
            }
        }
        return false;
    });
    $lines[] = $plugin . $plugininfo;
}
file_put_contents($pluginslistfile, implode("\n", $lines) . "\n");


function organise_dir($dir, $isodd) {
    if (!is_dir($dir)) {
        return '';
    }
    // Scan directory and find all .json files in it, remove extensions, sort the list as (float).
    $files = scandir($dir);
    $files = array_filter($files, function ($file) {
        return preg_match('/\.json$/', $file);
    });
    $files = array_map(function ($file) {
        return preg_replace('/\.json$/', '', $file);
    }, $files);
    usort($files, function ($a, $b) {
        return (float)$a - (float)$b;
    });

    // If any file is the same as the previous file - remove it.
    $lastfilecontents = null;
    $allversions = [];
    $functionnames = [];
    foreach ($files as $file) {
        $contents = file_get_contents($dir . '/' . $file . '.json');
        if ($contents !== $lastfilecontents) {
            $allversions[] = $file;
            $lastfilecontents = $contents;
            $json = json_decode($contents, true);
            $thisfunctionnames = array_map(function ($func) {
                return $func['name'];
            }, $json['functions']);
            $functionnames = array_merge($functionnames, $thisfunctionnames);
        } else {
            unlink($dir . '/' . $file . '.json');
        }
    }

    // Return the list of remaining files (without extensions) and all functions that have "odd" names.
    // "Odd" names are those that can be mistaken for belonging to other plugins.
    $functionnames = array_unique($functionnames);
    sort($functionnames);
    $oddfunctions = array_filter($functionnames, $isodd);

    return ':' . join(',', $allversions) . ($oddfunctions ? ':' . join(',', $oddfunctions) : '');
}