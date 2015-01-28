<?php
/**
 * Contains XmlCleaner class.
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
namespace XmlCleaner;

use DomainException;
use RuntimeException;
use SimpleXMLElement;
use tidy;
use XSLTProcessor;

/**
 * Class XmlCleaner
 */
class XmlCleaner
{
    /**
     * Find the default namespace use for elements without prefixes.
     *
     * Another method that isn't strictly needed as there are ways to get it but
     * nice to have access without having an instance of DOMDocument or
     * SimpleXMLElement active.
     *
     * @param  string $xml
     *
     * @return string
     * @throws DomainException
     * @throws RuntimeException
     */
    public function getDefaultNameSpace($xml = null)
    {
        if (null === $xml) {
            $xml = $this->getXml();
        }
        $xml = (string)$xml;
        $regex = '/xmlns\\s*=\\s*"([^"]+)"/';
        $result = preg_match($regex, $xml, $matches);
        if (0 === $result) {
            $mess = 'Default namespace is not set check the XML';
            throw new DomainException($mess);
        }
        return (string)$matches[1];
    }
    /**
     * Get the name of the root element.
     *
     * Not strictly needed as can be found direct with SimpleXML and DOM but
     * often needed and normal syntax isn't always clear on how to go about it.
     *
     * @param string $xml
     *
     * @return string
     */
    public function getRootElementName($xml = null)
    {
        if (null === $xml) {
            $xml = $this->getXml();
        }
        $xml = (string)$xml;
        $root = new SimpleXMLElement($xml);
        return $root->getName();
    }
    /**
     * @param string $xml
     * @param array  $config
     * @param string $encoding
     *
     * @return string
     * @throws RuntimeException
     */
    public function getTidyXml(
        $xml = null,
        array $config = null,
        $encoding = null
    ) {
        if (null === $xml) {
            $xml = $this->getXml();
        }
        $xml = (string)$xml;
        if (null === $config) {
            $config = $this->getTidyConfig();
        }
        if (null === $encoding) {
            $encoding = $this->getCharacterEncoding();
        }
        $encoding = (string)$encoding;
        $tidy = new tidy();
        $xml = $tidy->repairString($xml, $config, $encoding);
        return $xml;
    }
    /**
     * @return string
     * @throws RuntimeException
     */
    public function getXml()
    {
        if (0 == strlen($this->xml)) {
            $mess = 'XML MUST be set before it can be used';
            throw new RuntimeException($mess);
        }
        return $this->xml;
    }
    /**
     * Return a list of the namespaces listed in the XML.
     *
     * Note that the default namespace defined by xmlns="uri" is not returned
     * only the ones that look like xmlns:prefix="uri".
     *
     * @param string $xml
     * @param bool   $usePrefixKeys When true it returns the prefix as keys for
     *                              the Uri and the returned array will be
     *                              sorted by the prefix otherwise the array
     *                              order is undefined.
     *
     * @return array
     */
    public function getXmlNameSpaces($xml = null, $usePrefixKeys = true)
    {
        if (null === $xml) {
            $xml = $this->getXml();
        }
        $xml = (string)$xml;
        $regex = '/xmlns:(\w+)\\s*=\\s*"([^"]+)"/';
        preg_match_all($regex, $xml, $matches, PREG_SET_ORDER);
        if (0 === count($matches)) {
            return [];
        }
        $result = [];
        foreach ($matches as $nameSpace) {
            if ($usePrefixKeys) {
                $result[$nameSpace[1]] = $nameSpace[2];
                continue;
            }
            $result[] = $nameSpace[2];
        }
        if ($usePrefixKeys) {
            ksort($result);
        }
        return $result;
    }
    /**
     * Removes XML attributes with the given name space prefix.
     *
     * @param string $prefix Prefix of the name spaced attributes to be removed.
     * @param string $xml
     *
     * @return $this|string Returns result if given XML string or $this if not.
     * @throws DomainException
     * @throws RuntimeException
     */
    public function removeAttributesByPrefix($prefix, $xml = null)
    {
        $prefix = (string)$prefix;
        if (0 === strlen($prefix)) {
            $mess = 'Prefix can NOT be empty';
            throw new DomainException($mess);
        }
        $wasNull = false;
        if (null === $xml) {
            $xml = $this->getXml();
            $wasNull = true;
        }
        $xml = (string)$xml;
        if (false !== strpos($xml, $prefix . ':')) {
            $template = sprintf('<xsl:template match="@%s:*"/>', $prefix);
            $xml = $this->applyXslTemplates($xml, $template);
        }
        if ($wasNull) {
            $this->setXml($xml);
            return $this;
        }
        return $xml;
    }
    /**
     * Removes XML elements with the given name space prefix.
     *
     * @param string $prefix Prefix of the name spaced elements to be removed.
     * @param string $xml
     *
     * @return $this|string Returns result if given XML string or $this if not.
     * @throws DomainException
     * @throws RuntimeException
     */
    public function removeElementsByPrefix($prefix, $xml = null)
    {
        $prefix = (string)$prefix;
        if (0 === strlen($prefix)) {
            $mess = 'Prefix can NOT be empty';
            throw new DomainException($mess);
        }
        $wasNull = false;
        if (null === $xml) {
            $xml = $this->getXml();
            $wasNull = true;
        }
        $xml = (string)$xml;
        if (false !== strpos($xml, $prefix . ':')) {
            $template = sprintf('<xsl:template match="//%s:*"/>', $prefix);
            $xml = $this->applyXslTemplates($xml, $template);
        }
        if ($wasNull) {
            $this->setXml($xml);
            return $this;
        }
        return $xml;
    }
    /**
     * Remove any xmlns:prefix attributes from the XML if the prefix is unused
     * for any elements or attributes.
     *
     * @param string $xml
     *
     * @return string|$this Returns result if given XML string or $this if not.
     */
    public function removeUnusedNameSpaces($xml = null)
    {
        $wasNull = false;
        if (null === $xml) {
            $xml = $this->getXml();
            $wasNull = true;
        }
        $xml = (string)$xml;
        foreach (array_keys($this->getXmlNameSpaces($xml)) as $prefix) {
            if (false !== strpos($xml, $prefix . ':')) {
                continue;
            }
            $regex = sprintf('/(xmlns:%1$s\\s*=\\s*"[^"]+")/', $prefix);
            $xml = preg_replace($regex, '', $xml);
        }
        if ($wasNull) {
            $this->setXml($xml);
            return $this;
        }
        return $xml;
    }
    /**
     * @param string $xml
     *
     * @return $this|string Returns result if given XML string or $this if not.
     */
    public function removeXmlComments($xml = null)
    {
        $wasNull = false;
        if (null === $xml) {
            $xml = $this->getXml();
            $wasNull = true;
        }
        $xml = (string)$xml;
        $template = '<xsl:template match="comment()"/>';
        $xml = $this->applyXslTemplates($xml, $template);
        if ($wasNull) {
            $this->setXml($xml);
            return $this;
        }
        return $xml;
    }
    /**
     * Set character characterEncoding to be used.
     *
     * @param string $value
     *
     * @return XmlCleaner
     * @throws DomainException
     */
    public function setCharacterEncoding($value)
    {
        $knownEncodings
            = [
            'ascii',
            'latin0',
            'latin1',
            'raw',
            'utf8',
            'iso2022',
            'mac',
            'win1252',
            'ibm858',
            'utf16',
            'utf16le',
            'utf16be',
            'big5',
            'shiftjis'
        ];
        if (!in_array((string)$value, $knownEncodings, true)) {
            $mess = 'Unknown character characterEncoding, was given '
                    . (string)$value;
            throw new DomainException($mess);
        }
        $this->characterEncoding = $value;
        return $this;
    }
    /**
     * @param array $value
     *
     * @return self
     */
    public function setTidyConfig(array $value)
    {
        $this->tidyConfig = $value;
        return $this;
    }
    /**
     * @param string $value
     *
     * @return self
     */
    public function setXml($value)
    {
        $this->xml = (string)$value;
        return $this;
    }
    /**
     * @param string $xml
     * @param string $template
     *
     * @return string
     */
    protected function applyXslTemplates($xml, $template)
    {
        $nameSpaces = $this->getXmlNameSpaces($xml);
        $xmlNS = [''];
        if (0 !== count($nameSpaces)) {
            foreach ($nameSpaces as $prefix => $uri) {
                $xmlNS[] = sprintf('xmlns:%1$s="%2$s"', $prefix, $uri);
            }
        }
        $xslHead = '<xsl:transform %1$s version="1.0"'
                   . ' xmlns:xsl="http://www.w3.org/1999/XSL/Transform">'
                   . '<xsl:output method="xml" version="1.0" encoding="utf-8"'
                   . ' omit-xml-declaration="no" standalone="no" indent="no"/>';
        $xslFoot = '<xsl:template match="@*|node()">' . '<xsl:copy>'
                   . '<xsl:apply-templates select="@*|node()"/>'
                   . '</xsl:copy></xsl:template></xsl:transform>';
        $xsl = sprintf($xslHead, implode(' ', $xmlNS));
        $xsl .= $template . PHP_EOL . $xslFoot;
        $xslp = new XSLTProcessor();
        $xslp->importStylesheet(new SimpleXMLElement($xsl));
        $result = $xslp->transformToXml(new SimpleXMLElement($xml));
        return $result;
    }
    /**
     * @return string
     */
    protected function getCharacterEncoding()
    {
        return $this->characterEncoding;
    }
    /**
     * @return array
     */
    protected function getTidyConfig()
    {
        return $this->tidyConfig;
    }
    /**
     * The character characterEncoding to be used.
     *
     * Main place this is use is by Tidy for its output. Must being from the
     * $knownEncoding list.
     *
     * @type string $characterEncoding
     */
    protected $characterEncoding = 'utf8';
    /**
     * @type array $tidyConfig The configuration settings use by Tidy.
     */
    protected $tidyConfig
        = [
            'indent' => true,
            'indent-spaces' => 4,
            'output-xml' => true,
            'input-xml' => true,
            'wrap' => '1000'
        ];
    /**
     * @type string $xml Holds the XML that operations will be applied to.
     */
    protected $xml;
}
