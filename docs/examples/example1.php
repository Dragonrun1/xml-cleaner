<?php
/**
 * Contains XmlCleaner example1.
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
use XmlCleaner\XmlCleaner;

require_once dirname(dirname(__DIR__)) . '/bootstrap.php';
/*
 * In this example will show using XmlCleaner to clean up a SVG that has both
 * left over cruft from an application used to create it and some additional
 * manual edits.
 */
$svgName = __DIR__ . '/test1.svg';
$svg = file_get_contents($svgName);
$xc = new XmlCleaner();
/**
 * XmlCleaner uses a hybrid fluent interface. So how it works is after you have
 * used ```setXml($svg)``` you can string together all of the other methods
 * that effect the XML together without needing to pass the ```$xml``` parameter
 * to each of them but if you don't use ```setXml()``` each method will expect
 * to receive the ```$xml``` parameter and will perform its operation on it
 * instead and will return the result back to you directly and not store it in
 * the class property for father processing.
 */
/*
 * fluent interface example.
 */
$result = $xc->setXml($svg)
             ->removeXmlComments()
             ->removeUnusedNameSpaces()
             ->getTidyXml();
file_put_contents(__DIR__ . '/result1a.svg', $result);
/*
 * Non-fluent example.
 */
$result = $xc->removeXmlComments($svg);
$result = $xc->removeUnusedNameSpaces($result);
$result = $xc->getTidyXml($result);
file_put_contents(__DIR__ . '/result1b.svg', $result);
/*
 * Both will give the same result but the fluent one is easier to type and
 * understand any time you start use a lot of the methods together.
 */
