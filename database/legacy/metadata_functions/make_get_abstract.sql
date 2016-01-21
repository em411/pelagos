-- -----------------------------------------------------------------------------
-- Name:      make_get_metadata_abstract.sql
-- Author:    Patrick Krepps
-- Date:      02 October 2015
-- Inputs:    NONE
-- Output:    A new database function
-- Info:      This script creates the get_metadata_abstract function that
--            extracts the abstract element from an XML document.
-- -----------------------------------------------------------------------------
-- TODO:
-- -----------------------------------------------------------------------------

DROP FUNCTION udf_get_metadata_abstract(XML);

CREATE FUNCTION udf_get_metadata_abstract(metadata_doc XML)
RETURNS TEXT
AS $get_abstract$
   DECLARE
      _abstract              TEXT                := NULL;

   BEGIN
      EXECUTE
         'SELECT
             CAST((xpath(CONCAT(''/gmi:MI_Metadata'',
                                ''/gmd:identificationInfo'',
                                ''/gmd:MD_DataIdentification'',
                                ''/gmd:abstract'',
                                ''/gco:CharacterString/text()''),
                         $1,
                         ARRAY [ARRAY [''gco'',
                                       ''http://www.isotc211.org/2005/gco''],
                                ARRAY [''gmd'',
                                       ''http://www.isotc211.org/2005/gmd''],
                                ARRAY [''gmi'',
                                       ''http://www.isotc211.org/2005/gmi''],
                                ARRAY [''gml'',
                                       ''http://www.opengis.net/gml/3.2'']
                               ]
                        )
                  )[1] AS TEXT
                 )'
         INTO _abstract
         USING metadata_doc;
      RETURN _abstract;
   END;
$get_abstract$
LANGUAGE plpgsql IMMUTABLE STRICT;
