<?php
/*
 * This file is part of the PHPASN1 library.
 *
 * Copyright © Friedrich Große <friedrich.grosse@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FG\ASN1\Universal;

use Exception;
use FG\ASN1\Content;
use FG\ASN1\ElementBuilder;
use FG\ASN1\IdentifierManager;
use FG\ASN1\Object;
use FG\ASN1\Identifier;
use FG\ASN1\ContentLength;

class OctetString extends Object
{
    protected $value;

    public function __construct(Identifier $identifier, ContentLength $contentLength, Content $content, array $children = [])
    {

        parent::__construct($identifier, $contentLength, $content, $children);

        if(!$this->identifier->isConstructed) {
            $this->setValue($content);
        }
    }

    public function setValue(Content $content) {
        $value = $content->binaryData;
        if (is_string($value)) {
            // remove gaps between hex digits
            $value = preg_replace('/\s|0x/', '', $value);
        } elseif (is_numeric($value)) {
            $value = dechex($value);
        } else {
            throw new Exception('OctetString: unrecognized input type!');
        }

        if (strlen($value) % 2 != 0) {
            // transform values like 1F2 to 01F2
            $value = '0'.$value;
        }

        $this->value = $value;
    }

    protected function getEncodedValue()
    {
        $value = $this->value;
        $result = '';

        //Actual content
        while (strlen($value) >= 2) {
            // get the hex value byte by byte from the string and and add it to binary result
            $result .= chr(hexdec(substr($value, 0, 2)));
            $value = substr($value, 2);
        }

        return $result;
    }

    public function getStringValue()
    {
        return strtoupper(bin2hex($this->content->binaryData));
    }

    public static function encodeValue($value)
    {
        //данные в бинарном виде as is
        return $value;
    }

    public static function createFromBinaryString(string $binaryString, $options = [])
    {

        $isConstructed = $options['isConstructed'] ?? false;
        $lengthForm    = $options['lengthForm'] ?? ContentLength::INDEFINITE_FORM;

        return
            ElementBuilder::createObject(
                Identifier::CLASS_UNIVERSAL,
                Identifier::OCTETSTRING,
                $isConstructed,
                $binaryString,
                $lengthForm
            );
    }
}
