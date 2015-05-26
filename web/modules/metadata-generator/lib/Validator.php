<?php

namespace MetadataGenerator;

class XMLValidator
{
    public function validate($raw_xml)
    {
        $errors = 0;

        // create domdoc element and attempt to populate with supplied XML
        $doc = new \DomDocument('1.0', 'UTF-8');
        $tmpp = @$doc->loadXML($raw_xml);
        if (!$tmpp) {
            $errors++;
        }

        // attempt to validate XML per ISO-19115-2
        $schema = 'http://www.ngdc.noaa.gov/metadata/published/xsd/schema.xsd';
        if (!$doc->schemaValidate($schema)) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            for ($i=0; $i<sizeof($errors); $i++) {
                switch ($errors[$i]->level) {
                    case LIBXML_ERR_WARNING:
                        break;
                    case LIBXML_ERR_ERROR:
                        $errors++;
                        break;
                    case LIBXML_ERR_FATAL:
                        $errors++;
                        break;
                }
            }
        }
        if ($errors == 0) {
            return true;
        } else {
            return false;
        }
    }
}
