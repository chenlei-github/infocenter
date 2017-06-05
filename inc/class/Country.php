<?php

class Country
{
    const CONTINENT_NAMES = ['AS', 'EU', 'EE', 'NA', 'LA', 'OC', 'AF', 'UN'];

    const CONTINENT_LIST = [
        'AS' => ['AF', 'AM', 'AZ', 'BH', 'BD', 'BT', 'BN', 'KH', 'CN', 'CY', 'TL', 'GE', 'IN', 'ID', 'IQ', 'IL', 'JP', 'JO', 'KR', 'KW', 'KG', 'LA', 'LB', 'MY', 'MV', 'MN', 'NP', 'OM', 'PK', 'PS', 'PH', 'QA', 'SA', 'SG', 'LK', 'TW', 'TJ', 'TH', 'TR', 'TM', 'AE', 'UZ', 'VN', 'YE', 'HK', 'MO', 'IR', 'KP', 'MM'],
        'EU' => ['AD', 'AT', 'BE', 'DK', 'FO', 'FI', 'FR', 'DE', 'GI', 'GR', 'GB', 'IS', 'IE', 'IM', 'IT', 'LI', 'LU', 'MT', 'NL', 'NO', 'PT', 'SM', 'ES', 'SE', 'CH'],
        'EE' => ['AL', 'BY', 'BA', 'BG', 'HR', 'CZ', 'EE', 'HU', 'KS', 'LV', 'LT', 'MK', 'MD', 'ME', 'PL', 'RO', 'RU', 'RS', 'SK', 'SI', 'UA'],
        'NA' => ['BM', 'CA', 'GL', 'PM', 'US'],
        'LA' => ['AI', 'AG', 'AR', 'AW', 'BS', 'BB', 'BZ', 'BO', 'BR', 'VG', 'KY', 'CL', 'CO', 'CR', 'CU', 'DM', 'DO', 'EC', 'SV', 'GD', 'GP', 'GT', 'GY', 'HT', 'HN', 'JM', 'MX', 'MS', 'AN', 'NI', 'PA', 'PY', 'PE', 'PR', 'KN', 'LC', 'VC', 'SR', 'TT', 'TC', 'UY', 'VE'],
        'OC' => ['AS', 'AU', 'CK', 'FJ', 'PF', 'GU', 'KI', 'FM', 'NR', 'NC', 'NZ', 'NU', 'NF', 'PW', 'PG', 'WS', 'SB', 'TO', 'TV', 'VU'],
        'AF' => ['DZ', 'AO', 'BJ', 'BW', 'BF', 'BI', 'CM', 'CV', 'CF', 'TD', 'KM', 'CG', 'CD', 'DJ', 'EG', 'GQ', 'ER', 'ET', 'GA', 'GM', 'GH', 'GN', 'GW', 'CI', 'KE', 'LS', 'LR', 'LY', 'MG', 'MW', 'ML', 'MR', 'MU', 'MA', 'MZ', 'NA', 'NE', 'NG', 'RE', 'RW', 'ST', 'SN', 'SC', 'SL', 'SO', 'ZA', 'SZ', 'TZ', 'TG', 'TN', 'UG', 'ZM', 'ZW', 'SS', 'SD', 'SY'],
        'UN' => ['UN'],
    ];

    const COUNTRY_LIST = [
        'UN' => [
            'name'          => 'unknown',
            'continentCode' => 'UN',
            'mask'          => 1,
        ],
        'AF' => [
            'name'          => 'Afghanistan',
            'continentCode' => 'AS',
            'mask'          => 1,
        ],
        'AL' => [
            'name'          => 'Albania',
            'continentCode' => 'EE',
            'mask'          => 1,
        ],
        'DZ' => [
            'name'          => 'Algeria',
            'continentCode' => 'AF',
            'mask'          => 1,
        ],
        'AS' => [
            'name'          => 'American Samoa (United States of America)',
            'continentCode' => 'OC',
            'mask'          => 1,
        ],
        'AD' => [
            'name'          => 'Andorra',
            'continentCode' => 'EU',
            'mask'          => 1,
        ],
        'AO' => [
            'name'          => 'Angola',
            'continentCode' => 'AF',
            'mask'          => 2,
        ],
        'AI' => [
            'name'          => 'Anguilla (United Kingdom)',
            'continentCode' => 'LA',
            'mask'          => 1,
        ],
        'AG' => [
            'name'          => 'Antigua and Barbuda',
            'continentCode' => 'LA',
            'mask'          => 2,
        ],
        'AR' => [
            'name'          => 'Argentina',
            'continentCode' => 'LA',
            'mask'          => 4,
        ],
        'AM' => [
            'name'          => 'Armenia',
            'continentCode' => 'AS',
            'mask'          => 2,
        ],
        'AW' => [
            'name'          => 'Aruba (Kingdom of the Netherlands)',
            'continentCode' => 'LA',
            'mask'          => 8,
        ],
        'AU' => [
            'name'          => 'Australia',
            'continentCode' => 'OC',
            'mask'          => 2,
        ],
        'AT' => [
            'name'          => 'Austria',
            'continentCode' => 'EU',
            'mask'          => 2,
        ],
        'AZ' => [
            'name'          => 'Azerbaijan',
            'continentCode' => 'AS',
            'mask'          => 4,
        ],
        'BS' => [
            'name'          => 'Bahamas',
            'continentCode' => 'LA',
            'mask'          => 16,
        ],
        'BH' => [
            'name'          => 'Bahrain',
            'continentCode' => 'AS',
            'mask'          => 8,
        ],
        'BD' => [
            'name'          => 'Bangladesh',
            'continentCode' => 'AS',
            'mask'          => 16,
        ],
        'BB' => [
            'name'          => 'Barbados',
            'continentCode' => 'LA',
            'mask'          => 32,
        ],
        'BY' => [
            'name'          => 'Belarus',
            'continentCode' => 'EE',
            'mask'          => 2,
        ],
        'BE' => [
            'name'          => 'Belgium',
            'continentCode' => 'EU',
            'mask'          => 4,
        ],
        'BZ' => [
            'name'          => 'Belize',
            'continentCode' => 'LA',
            'mask'          => 64,
        ],
        'BJ' => [
            'name'          => 'Benin',
            'continentCode' => 'AF',
            'mask'          => 4,
        ],
        'BM' => [
            'name'          => 'Bermuda',
            'continentCode' => 'NA',
            'mask'          => 1,
        ],
        'BT' => [
            'name'          => 'Bhutan',
            'continentCode' => 'AS',
            'mask'          => 32,
        ],
        'BO' => [
            'name'          => 'Bolivia',
            'continentCode' => 'LA',
            'mask'          => 128,
        ],
        'BA' => [
            'name'          => 'Bosnia and Herzegovina',
            'continentCode' => 'EE',
            'mask'          => 4,
        ],
        'BW' => [
            'name'          => 'Botswana',
            'continentCode' => 'AF',
            'mask'          => 8,
        ],
        'BR' => [
            'name'          => 'Brazil',
            'continentCode' => 'LA',
            'mask'          => 256,
        ],
        'VG' => [
            'name'          => 'British Virgin Islands (United Kingdom)',
            'continentCode' => 'LA',
            'mask'          => 512,
        ],
        'BN' => [
            'name'          => 'Brunei',
            'continentCode' => 'AS',
            'mask'          => 64,
        ],
        'BG' => [
            'name'          => 'Bulgaria',
            'continentCode' => 'EE',
            'mask'          => 8,
        ],
        'BF' => [
            'name'          => 'Burkina Faso',
            'continentCode' => 'AF',
            'mask'          => 16,
        ],
        'BI' => [
            'name'          => 'Burundi',
            'continentCode' => 'AF',
            'mask'          => 32,
        ],
        'KH' => [
            'name'          => 'Cambodia',
            'continentCode' => 'AS',
            'mask'          => 128,
        ],
        'CM' => [
            'name'          => 'Cameroon',
            'continentCode' => 'AF',
            'mask'          => 64,
        ],
        'CA' => [
            'name'          => 'Canada',
            'continentCode' => 'NA',
            'mask'          => 2,
        ],
        'CV' => [
            'name'          => 'Cape Verde',
            'continentCode' => 'AF',
            'mask'          => 128,
        ],
        'KY' => [
            'name'          => 'Cayman Islands (United Kingdom)',
            'continentCode' => 'LA',
            'mask'          => 1024,
        ],
        'CF' => [
            'name'          => 'Central African Republic',
            'continentCode' => 'AF',
            'mask'          => 256,
        ],
        'TD' => [
            'name'          => 'Chad',
            'continentCode' => 'AF',
            'mask'          => 512,
        ],
        'CL' => [
            'name'          => 'Chile',
            'continentCode' => 'LA',
            'mask'          => 2048,
        ],
        'CN' => [
            'name'          => 'China',
            'continentCode' => 'AS',
            'mask'          => 256,
        ],
        'CO' => [
            'name'          => 'Colombia',
            'continentCode' => 'LA',
            'mask'          => 4096,
        ],
        'KM' => [
            'name'          => 'Comoros',
            'continentCode' => 'AF',
            'mask'          => 1024,
        ],
        'CG' => [
            'name'          => 'Republic of the Congo',
            'continentCode' => 'AF',
            'mask'          => 2048,
        ],
        'CK' => [
            'name'          => 'Cook Islands (New Zealand)',
            'continentCode' => 'OC',
            'mask'          => 4,
        ],
        'CR' => [
            'name'          => 'Costa Rica',
            'continentCode' => 'LA',
            'mask'          => 8192,
        ],
        'HR' => [
            'name'          => 'Croatia',
            'continentCode' => 'EE',
            'mask'          => 16,
        ],
        'CU' => [
            'name'          => 'Cuba',
            'continentCode' => 'LA',
            'mask'          => 16384,
        ],
        'CY' => [
            'name'          => 'Cyprus',
            'continentCode' => 'AS',
            'mask'          => 512,
        ],
        'CZ' => [
            'name'          => 'Czech Republic',
            'continentCode' => 'EE',
            'mask'          => 32,
        ],
        'CD' => [
            'name'          => 'Democratic Republic of the Congo',
            'continentCode' => 'AF',
            'mask'          => 4096,
        ],
        'DK' => [
            'name'          => 'Denmark (Kingdom of Denmark)',
            'continentCode' => 'EU',
            'mask'          => 8,
        ],
        'DJ' => [
            'name'          => 'Djibouti',
            'continentCode' => 'AF',
            'mask'          => 8192,
        ],
        'DM' => [
            'name'          => 'Dominica',
            'continentCode' => 'LA',
            'mask'          => 32768,
        ],
        'DO' => [
            'name'          => 'Dominican Republic',
            'continentCode' => 'LA',
            'mask'          => 65536,
        ],
        'TL' => [
            'name'          => 'East Timor',
            'continentCode' => 'AS',
            'mask'          => 1024,
        ],
        'EC' => [
            'name'          => 'Ecuador',
            'continentCode' => 'LA',
            'mask'          => 131072,
        ],
        'EG' => [
            'name'          => 'Egypt',
            'continentCode' => 'AF',
            'mask'          => 16384,
        ],
        'SV' => [
            'name'          => 'El Salvador',
            'continentCode' => 'LA',
            'mask'          => 262144,
        ],
        'GQ' => [
            'name'          => 'Equatorial Guinea',
            'continentCode' => 'AF',
            'mask'          => 32768,
        ],
        'ER' => [
            'name'          => 'Eritrea',
            'continentCode' => 'AF',
            'mask'          => 65536,
        ],
        'EE' => [
            'name'          => 'Estonia',
            'continentCode' => 'EE',
            'mask'          => 64,
        ],
        'ET' => [
            'name'          => 'Ethiopia',
            'continentCode' => 'AF',
            'mask'          => 131072,
        ],
        'FO' => [
            'name'          => 'Faroe Islands (Kingdom of Denmark)',
            'continentCode' => 'EU',
            'mask'          => 16,
        ],
        'FJ' => [
            'name'          => 'Fiji',
            'continentCode' => 'OC',
            'mask'          => 8,
        ],
        'FI' => [
            'name'          => 'Finland',
            'continentCode' => 'EU',
            'mask'          => 32,
        ],
        'FR' => [
            'name'          => 'France',
            'continentCode' => 'EU',
            'mask'          => 64,
        ],
        'PF' => [
            'name'          => 'French Polynesia (France)',
            'continentCode' => 'OC',
            'mask'          => 16,
        ],
        'GA' => [
            'name'          => 'Gabon',
            'continentCode' => 'AF',
            'mask'          => 262144,
        ],
        'GM' => [
            'name'          => 'Gambia',
            'continentCode' => 'AF',
            'mask'          => 524288,
        ],
        'GE' => [
            'name'          => 'Georgia',
            'continentCode' => 'AS',
            'mask'          => 2048,
        ],
        'DE' => [
            'name'          => 'Germany',
            'continentCode' => 'EU',
            'mask'          => 128,
        ],
        'GH' => [
            'name'          => 'Ghana',
            'continentCode' => 'AF',
            'mask'          => 1048576,
        ],
        'GI' => [
            'name'          => 'Gibraltar (United Kingdom)',
            'continentCode' => 'EU',
            'mask'          => 256,
        ],
        'GR' => [
            'name'          => 'Greece',
            'continentCode' => 'EU',
            'mask'          => 512,
        ],
        'GL' => [
            'name'          => 'Greenland (Kingdom of Denmark)',
            'continentCode' => 'NA',
            'mask'          => 4,
        ],
        'GD' => [
            'name'          => 'Grenada',
            'continentCode' => 'LA',
            'mask'          => 524288,
        ],
        'GP' => [
            'name'          => 'Guadeloupe (France)',
            'continentCode' => 'LA',
            'mask'          => 1048576,
        ],
        'GU' => [
            'name'          => 'Guam (United States of America)',
            'continentCode' => 'OC',
            'mask'          => 32,
        ],
        'GT' => [
            'name'          => 'Guatemala',
            'continentCode' => 'LA',
            'mask'          => 2097152,
        ],
        'GB' => [
            'name'          => 'United Kingdom',
            'continentCode' => 'EU',
            'mask'          => 1024,
        ],
        'GN' => [
            'name'          => 'Guinea',
            'continentCode' => 'AF',
            'mask'          => 2097152,
        ],
        'GW' => [
            'name'          => 'Guinea-Bissau',
            'continentCode' => 'AF',
            'mask'          => 4194304,
        ],
        'GY' => [
            'name'          => 'Guyana',
            'continentCode' => 'LA',
            'mask'          => 4194304,
        ],
        'HT' => [
            'name'          => 'Haiti',
            'continentCode' => 'LA',
            'mask'          => 8388608,
        ],
        'HN' => [
            'name'          => 'Honduras',
            'continentCode' => 'LA',
            'mask'          => 16777216,
        ],
        'HU' => [
            'name'          => 'Hungary',
            'continentCode' => 'EE',
            'mask'          => 128,
        ],
        'IS' => [
            'name'          => 'Iceland',
            'continentCode' => 'EU',
            'mask'          => 2048,
        ],
        'IN' => [
            'name'          => 'India',
            'continentCode' => 'AS',
            'mask'          => 4096,
        ],
        'ID' => [
            'name'          => 'Indonesia',
            'continentCode' => 'AS',
            'mask'          => 8192,
        ],
        'IR' => [
            'name'          => 'Iran',
            'continentCode' => 'AS',
            'mask'          => 70368744177664,
        ],
        'IQ' => [
            'name'          => 'Iraq',
            'continentCode' => 'AS',
            'mask'          => 16384,
        ],
        'IE' => [
            'name'          => 'Ireland',
            'continentCode' => 'EU',
            'mask'          => 4096,
        ],
        'IM' => [
            'name'          => 'Isle of Man (United Kingdom)',
            'continentCode' => 'EU',
            'mask'          => 8192,
        ],
        'IL' => [
            'name'          => 'Israel',
            'continentCode' => 'AS',
            'mask'          => 32768,
        ],
        'IT' => [
            'name'          => 'Italy',
            'continentCode' => 'EU',
            'mask'          => 16384,
        ],
        'CI' => [
            'name'          => 'Ivory Coast',
            'continentCode' => 'AF',
            'mask'          => 8388608,
        ],
        'JM' => [
            'name'          => 'Jamaica',
            'continentCode' => 'LA',
            'mask'          => 33554432,
        ],
        'JP' => [
            'name'          => 'Japan',
            'continentCode' => 'AS',
            'mask'          => 65536,
        ],
        'JO' => [
            'name'          => 'Jordan',
            'continentCode' => 'AS',
            'mask'          => 131072,
        ],
        'KE' => [
            'name'          => 'Kenya',
            'continentCode' => 'AF',
            'mask'          => 16777216,
        ],
        'KI' => [
            'name'          => 'Kiribati',
            'continentCode' => 'OC',
            'mask'          => 64,
        ],
        'KP' => [
            'name'          => 'North Korea',
            'continentCode' => 'AS',
            'mask'          => 140737488355328,
        ],
        'KR' => [
            'name'          => 'South Korea',
            'continentCode' => 'AS',
            'mask'          => 262144,
        ],
        'KS' => [
            'name'          => 'Kosovo',
            'continentCode' => 'EE',
            'mask'          => 256,
        ],
        'KW' => [
            'name'          => 'Kuwait',
            'continentCode' => 'AS',
            'mask'          => 524288,
        ],
        'KG' => [
            'name'          => 'Kyrgyzstan',
            'continentCode' => 'AS',
            'mask'          => 1048576,
        ],
        'LA' => [
            'name'          => 'Laos',
            'continentCode' => 'AS',
            'mask'          => 2097152,
        ],
        'LV' => [
            'name'          => 'Latvia',
            'continentCode' => 'EE',
            'mask'          => 512,
        ],
        'LB' => [
            'name'          => 'Lebanon',
            'continentCode' => 'AS',
            'mask'          => 4194304,
        ],
        'LS' => [
            'name'          => 'Lesotho',
            'continentCode' => 'AF',
            'mask'          => 33554432,
        ],
        'LR' => [
            'name'          => 'Liberia',
            'continentCode' => 'AF',
            'mask'          => 67108864,
        ],
        'LY' => [
            'name'          => 'Libya',
            'continentCode' => 'AF',
            'mask'          => 134217728,
        ],
        'LI' => [
            'name'          => 'Liechtenstein',
            'continentCode' => 'EU',
            'mask'          => 32768,
        ],
        'LT' => [
            'name'          => 'Lithuania',
            'continentCode' => 'EE',
            'mask'          => 1024,
        ],
        'LU' => [
            'name'          => 'Luxembourg',
            'continentCode' => 'EU',
            'mask'          => 65536,
        ],
        'MK' => [
            'name'          => 'Macedonia',
            'continentCode' => 'EE',
            'mask'          => 2048,
        ],
        'MG' => [
            'name'          => 'Madagascar',
            'continentCode' => 'AF',
            'mask'          => 268435456,
        ],
        'MW' => [
            'name'          => 'Malawi',
            'continentCode' => 'AF',
            'mask'          => 536870912,
        ],
        'MY' => [
            'name'          => 'Malaysia',
            'continentCode' => 'AS',
            'mask'          => 8388608,
        ],
        'MV' => [
            'name'          => 'Maldives',
            'continentCode' => 'AS',
            'mask'          => 16777216,
        ],
        'ML' => [
            'name'          => 'Mali',
            'continentCode' => 'AF',
            'mask'          => 1073741824,
        ],
        'MT' => [
            'name'          => 'Malta',
            'continentCode' => 'EU',
            'mask'          => 131072,
        ],
        'MR' => [
            'name'          => 'Mauritania',
            'continentCode' => 'AF',
            'mask'          => 2147483648,
        ],
        'MU' => [
            'name'          => 'Mauritius',
            'continentCode' => 'AF',
            'mask'          => 4294967296,
        ],
        'MX' => [
            'name'          => 'Mexico',
            'continentCode' => 'LA',
            'mask'          => 67108864,
        ],
        'FM' => [
            'name'          => 'Federated States of Micronesia',
            'continentCode' => 'OC',
            'mask'          => 128,
        ],
        'MD' => [
            'name'          => 'Moldova',
            'continentCode' => 'EE',
            'mask'          => 4096,
        ],
        'MN' => [
            'name'          => 'Mongolia',
            'continentCode' => 'AS',
            'mask'          => 33554432,
        ],
        'ME' => [
            'name'          => 'Montenegro',
            'continentCode' => 'EE',
            'mask'          => 8192,
        ],
        'MS' => [
            'name'          => 'Montserrat (United Kingdom)',
            'continentCode' => 'LA',
            'mask'          => 134217728,
        ],
        'MA' => [
            'name'          => 'Morocco',
            'continentCode' => 'AF',
            'mask'          => 8589934592,
        ],
        'MZ' => [
            'name'          => 'Mozambique',
            'continentCode' => 'AF',
            'mask'          => 17179869184,
        ],
        'MM' => [
            'name'          => 'Myanmar',
            'continentCode' => 'AS',
            'mask'          => 281474976710656,
        ],
        'NA' => [
            'name'          => 'Namibia',
            'continentCode' => 'AF',
            'mask'          => 34359738368,
        ],
        'NR' => [
            'name'          => 'Nauru',
            'continentCode' => 'OC',
            'mask'          => 256,
        ],
        'NP' => [
            'name'          => 'Nepal',
            'continentCode' => 'AS',
            'mask'          => 67108864,
        ],
        'NL' => [
            'name'          => 'Netherlands (Kingdom of the Netherlands)',
            'continentCode' => 'EU',
            'mask'          => 262144,
        ],
        'AN' => [
            'name'          => 'Netherlands Antilles (Kingdom of the Netherlands)',
            'continentCode' => 'LA',
            'mask'          => 268435456,
        ],
        'NC' => [
            'name'          => 'New Caledonia (France)',
            'continentCode' => 'OC',
            'mask'          => 512,
        ],
        'NZ' => [
            'name'          => 'New Zealand',
            'continentCode' => 'OC',
            'mask'          => 1024,
        ],
        'NI' => [
            'name'          => 'Nicaragua',
            'continentCode' => 'LA',
            'mask'          => 536870912,
        ],
        'NE' => [
            'name'          => 'Niger',
            'continentCode' => 'AF',
            'mask'          => 68719476736,
        ],
        'NG' => [
            'name'          => 'Nigeria',
            'continentCode' => 'AF',
            'mask'          => 137438953472,
        ],
        'NU' => [
            'name'          => 'Niue',
            'continentCode' => 'OC',
            'mask'          => 2048,
        ],
        'NF' => [
            'name'          => 'Norfolk Island',
            'continentCode' => 'OC',
            'mask'          => 4096,
        ],
        'NO' => [
            'name'          => 'Norway',
            'continentCode' => 'EU',
            'mask'          => 524288,
        ],
        'OM' => [
            'name'          => 'Oman',
            'continentCode' => 'AS',
            'mask'          => 134217728,
        ],
        'PK' => [
            'name'          => 'Pakistan',
            'continentCode' => 'AS',
            'mask'          => 268435456,
        ],
        'PW' => [
            'name'          => 'Palau',
            'continentCode' => 'OC',
            'mask'          => 8192,
        ],
        'PS' => [
            'name'          => 'Palestinian Authority',
            'continentCode' => 'AS',
            'mask'          => 536870912,
        ],
        'PA' => [
            'name'          => 'Panama',
            'continentCode' => 'LA',
            'mask'          => 1073741824,
        ],
        'PG' => [
            'name'          => 'Papua New Guinea',
            'continentCode' => 'OC',
            'mask'          => 16384,
        ],
        'PY' => [
            'name'          => 'Paraguay',
            'continentCode' => 'LA',
            'mask'          => 2147483648,
        ],
        'PE' => [
            'name'          => 'Peru',
            'continentCode' => 'LA',
            'mask'          => 4294967296,
        ],
        'PH' => [
            'name'          => 'Philippines',
            'continentCode' => 'AS',
            'mask'          => 1073741824,
        ],
        'PL' => [
            'name'          => 'Poland',
            'continentCode' => 'EE',
            'mask'          => 16384,
        ],
        'PT' => [
            'name'          => 'Portugal',
            'continentCode' => 'EU',
            'mask'          => 1048576,
        ],
        'PR' => [
            'name'          => 'Puerto Rico',
            'continentCode' => 'LA',
            'mask'          => 8589934592,
        ],
        'QA' => [
            'name'          => 'Qatar',
            'continentCode' => 'AS',
            'mask'          => 2147483648,
        ],
        'RE' => [
            'name'          => 'Réunion (France)',
            'continentCode' => 'AF',
            'mask'          => 274877906944,
        ],
        'RO' => [
            'name'          => 'Romania',
            'continentCode' => 'EE',
            'mask'          => 32768,
        ],
        'RU' => [
            'name'          => 'Russian Federation',
            'continentCode' => 'EE',
            'mask'          => 65536,
        ],
        'RW' => [
            'name'          => 'Rwanda',
            'continentCode' => 'AF',
            'mask'          => 549755813888,
        ],
        'KN' => [
            'name'          => 'Saint Kitts and Nevis',
            'continentCode' => 'LA',
            'mask'          => 17179869184,
        ],
        'LC' => [
            'name'          => 'Saint Lucia',
            'continentCode' => 'LA',
            'mask'          => 34359738368,
        ],
        'PM' => [
            'name'          => 'Saint Pierre and Miquelon (France)',
            'continentCode' => 'NA',
            'mask'          => 8,
        ],
        'VC' => [
            'name'          => 'Saint Vincent and the Grenadines',
            'continentCode' => 'LA',
            'mask'          => 68719476736,
        ],
        'WS' => [
            'name'          => 'Samoa',
            'continentCode' => 'OC',
            'mask'          => 32768,
        ],
        'SM' => [
            'name'          => 'San Marino',
            'continentCode' => 'EU',
            'mask'          => 2097152,
        ],
        'ST' => [
            'name'          => 'Sao Tome and Principe',
            'continentCode' => 'AF',
            'mask'          => 1099511627776,
        ],
        'SA' => [
            'name'          => 'Saudi Arabia',
            'continentCode' => 'AS',
            'mask'          => 4294967296,
        ],
        'SN' => [
            'name'          => 'Senegal',
            'continentCode' => 'AF',
            'mask'          => 2199023255552,
        ],
        'RS' => [
            'name'          => 'Serbia',
            'continentCode' => 'EE',
            'mask'          => 131072,
        ],
        'SC' => [
            'name'          => 'Seychelles',
            'continentCode' => 'AF',
            'mask'          => 4398046511104,
        ],
        'SL' => [
            'name'          => 'Sierra Leone',
            'continentCode' => 'AF',
            'mask'          => 8796093022208,
        ],
        'SG' => [
            'name'          => 'Singapore',
            'continentCode' => 'AS',
            'mask'          => 8589934592,
        ],
        'SK' => [
            'name'          => 'Slovakia',
            'continentCode' => 'EE',
            'mask'          => 262144,
        ],
        'SI' => [
            'name'          => 'Slovenia',
            'continentCode' => 'EE',
            'mask'          => 524288,
        ],
        'SB' => [
            'name'          => 'Solomon Islands',
            'continentCode' => 'OC',
            'mask'          => 65536,
        ],
        'SO' => [
            'name'          => 'Somalia',
            'continentCode' => 'AF',
            'mask'          => 17592186044416,
        ],
        'ZA' => [
            'name'          => 'South Africa',
            'continentCode' => 'AF',
            'mask'          => 35184372088832,
        ],
        'SS' => [
            'name'          => 'South Sudan',
            'continentCode' => 'AF',
            'mask'          => 9007199254740992,
        ],
        'ES' => [
            'name'          => 'Spain',
            'continentCode' => 'EU',
            'mask'          => 4194304,
        ],
        'LK' => [
            'name'          => 'Sri Lanka',
            'continentCode' => 'AS',
            'mask'          => 17179869184,
        ],
        'SD' => [
            'name'          => 'Sudan',
            'continentCode' => 'AF',
            'mask'          => 18014398509481984,
        ],
        'SR' => [
            'name'          => 'Suriname',
            'continentCode' => 'LA',
            'mask'          => 137438953472,
        ],
        'SZ' => [
            'name'          => 'Swaziland',
            'continentCode' => 'AF',
            'mask'          => 70368744177664,
        ],
        'SE' => [
            'name'          => 'Sweden',
            'continentCode' => 'EU',
            'mask'          => 8388608,
        ],
        'CH' => [
            'name'          => 'Switzerland',
            'continentCode' => 'EU',
            'mask'          => 16777216,
        ],
        'SY' => [
            'name'          => 'Syria',
            'continentCode' => 'AF',
            'mask'          => 36028797018963968,
        ],
        'TW' => [
            'name'          => 'Taiwan',
            'continentCode' => 'AS',
            'mask'          => 34359738368,
        ],
        'TJ' => [
            'name'          => 'Tajikistan',
            'continentCode' => 'AS',
            'mask'          => 68719476736,
        ],
        'TZ' => [
            'name'          => 'Tanzania',
            'continentCode' => 'AF',
            'mask'          => 140737488355328,
        ],
        'TH' => [
            'name'          => 'Thailand',
            'continentCode' => 'AS',
            'mask'          => 137438953472,
        ],
        'TG' => [
            'name'          => 'Togo',
            'continentCode' => 'AF',
            'mask'          => 281474976710656,
        ],
        'TO' => [
            'name'          => 'Tonga',
            'continentCode' => 'OC',
            'mask'          => 131072,
        ],
        'TT' => [
            'name'          => 'Trinidad and Tobago',
            'continentCode' => 'LA',
            'mask'          => 274877906944,
        ],
        'TN' => [
            'name'          => 'Tunisia',
            'continentCode' => 'AF',
            'mask'          => 562949953421312,
        ],
        'TR' => [
            'name'          => 'Turkey',
            'continentCode' => 'AS',
            'mask'          => 274877906944,
        ],
        'TM' => [
            'name'          => 'Turkmenistan',
            'continentCode' => 'AS',
            'mask'          => 549755813888,
        ],
        'TC' => [
            'name'          => 'Turks and Caicos Islands',
            'continentCode' => 'LA',
            'mask'          => 549755813888,
        ],
        'TV' => [
            'name'          => 'Tuvalu',
            'continentCode' => 'OC',
            'mask'          => 262144,
        ],
        'UG' => [
            'name'          => 'Uganda',
            'continentCode' => 'AF',
            'mask'          => 1125899906842624,
        ],
        'UA' => [
            'name'          => 'Ukraine',
            'continentCode' => 'EE',
            'mask'          => 1048576,
        ],
        'AE' => [
            'name'          => 'United Arab Emirates',
            'continentCode' => 'AS',
            'mask'          => 1099511627776,
        ],
        'US' => [
            'name'          => 'United States of America',
            'continentCode' => 'NA',
            'mask'          => 16,
        ],
        'UY' => [
            'name'          => 'Uruguay',
            'continentCode' => 'LA',
            'mask'          => 1099511627776,
        ],
        'UZ' => [
            'name'          => 'Uzbekistan',
            'continentCode' => 'AS',
            'mask'          => 2199023255552,
        ],
        'VU' => [
            'name'          => 'Vanuatu',
            'continentCode' => 'OC',
            'mask'          => 524288,
        ],
        'VE' => [
            'name'          => 'Venezuela',
            'continentCode' => 'LA',
            'mask'          => 2199023255552,
        ],
        'VN' => [
            'name'          => 'Vietnam',
            'continentCode' => 'AS',
            'mask'          => 4398046511104,
        ],
        'YE' => [
            'name'          => 'Yemen',
            'continentCode' => 'AS',
            'mask'          => 8796093022208,
        ],
        'ZM' => [
            'name'          => 'Zambia',
            'continentCode' => 'AF',
            'mask'          => 2251799813685248,
        ],
        'ZW' => [
            'name'          => 'Zimbabwe',
            'continentCode' => 'AF',
            'mask'          => 4503599627370496,
        ],
        'HK' => [
            'name'          => 'Hong Kong',
            'continentCode' => 'AS',
            'mask'          => 17592186044416,
        ],
        'MO' => [
            'name'          => 'Macau',
            'continentCode' => 'AS',
            'mask'          => 35184372088832,
        ],
    ];

    private function __construct()
    {
        return false;
    }

    public static function getMaskOfCountry($countryCode)
    {
        if (array_key_exists($countryCode, self::COUNTRY_LIST)) {
            return self::COUNTRY_LIST[$countryCode]['mask'];
        } else {
            return null;
        }
    }

    public static function getMaskListOfCountry()
    {
        $mask = [];
        foreach (self::CONTINENT_LIST as $key => $c_list) {
            $mask[$key] = [];

            $i = 0;
            foreach ($c_list as $c) {
                $mask[$key][$c] = 1 << $i;
                $i++;
            }
        }

        return $mask;
    }

    public static function getContinentOfCountry($country)
    {
        if (array_key_exists($country, self::COUNTRY_LIST)) {
            return self::COUNTRY_LIST[$country]['continentCode'];
        } else {
            return 'UN';
        }
    }
}
