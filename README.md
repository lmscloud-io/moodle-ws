# moodle-ws-catalog

A comprehensive, open-source catalog of all web service functions available across Moodle core and selected community plugins hosted on moodle.org/plugins.

## Overview
This project automatically scans Moodle core and some community plugins to extract:
- All available web service functions
- Their parameters and return structures

New description file is created for each version that has modifications in web service definitions.

The catalog is intended for developers, integrators, and tooling authors who need an up‑to‑date reference of Moodle's external APIs.

The catalog uses CLI script from [tool_wsdiscovery](https://github.com/lmscloud-io/moodle-tool_wsdiscovery) plugin to generate JSON descriptions of web service functions.

## Adding a Community Plugin

To add a community plugin to this catalog, first ensure that the plugin is available on https://moodle.org/plugins. Create a pull request adding the plugin name as a separate line in the file [pluginslist.txt](pluginslist.txt), preferably in the correct alphabetical position. Do not include a semicolon or version list - these will be added automatically by the script when the plugin is processed. After the pull request is accepted, the plugin's web services will be indexed within several hours.

If you want to add plugins that are not open source, create an issue and we can discuss the process there.

## Status
The project is in active development. Contributions, issues, and suggestions are welcome.

## Repository Name
This project is **not affiliated with or endorsed by Moodle HQ**. The use of the word "Moodle" follows their trademark guidelines for descriptive, non‑commercial usage.

## License
This project is licensed under the **GNU General Public License v3 (GPLv3)**. See `LICENSE.txt` for details.

## Contributing
Pull requests are welcome!

## Acknowledgments
Thanks to the Moodle community and plugin developers for maintaining an extensive ecosystem of open-source tools and services.

