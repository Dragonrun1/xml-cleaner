<?php
/**
 * Contains XmlCleaner Bootstrap.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of XmlCleaner - A wrapper for some XML utilities to make
 * deleting name spaces, their elements and attributes easier.
 * Copyright (C) 2015 Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE file.
 *
 * @copyright 2015 Michael Cummings
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU GPLv2
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
/*
 * Find auto loader from one of
 * vendor/bin/
 * OR ./
 * OR bin/
 * OR lib/project/
 * OR vendor/project/project/bin/
 */
(@include_once dirname(__DIR__) . '/autoload.php')
|| (@include_once __DIR__ . '/vendor/autoload.php')
|| (@include_once dirname(__DIR__) . '/vendor/autoload.php')
|| (@include_once dirname(dirname(__DIR__)) . '/vendor/autoload.php')
|| (@include_once dirname(dirname(dirname(__DIR__))) . '/autoload.php')
|| die('Could not find required auto class loader. Aborting ...');
