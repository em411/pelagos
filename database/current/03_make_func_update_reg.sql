-- -----------------------------------------------------------------------------
-- Name:      udf_update_reg()
-- Author:    Patrick N. Krepps Jr.
-- Date:      02 September 2015
-- Purpose    This function creates a BEFORE INSERT trigger on the registry
--            table that determines if a previous registry entry exists for this
--            insert's UDI, and copies the information from that record to this
--            new record. This is necessary because the existing system does not
--            have a mechanism for updating a record. Instead, updates cause a
--            new record to be created that is appended with an incrementing
--            sequence. Seriously.
-- -----------------------------------------------------------------------------
-- TODO:      
-- -----------------------------------------------------------------------------
DROP TRIGGER IF EXISTS udf_update_reg_trigger ON registry;
DROP FUNCTION IF EXISTS udf_update_reg();

CREATE OR REPLACE FUNCTION udf_update_reg()
RETURNS TRIGGER
AS $$
   DECLARE
      _DEBUG       CONSTANT BOOLEAN    := FALSE;
      _nreg_seq    INTEGER;
      _oreg_id     CHARACTER(20);
      _oreg_seq    CHAR(3);
      _reg_row     registry%ROWTYPE;

   BEGIN
      -- Start by getting the last digits of the new registry_id:
      _nreg_seq := CAST(SUBSTRING(NEW.registry_id FROM 18 FOR 3) AS INTEGER);
      IF _DEBUG
      THEN
         RAISE NOTICE '_nreg_seq: %', _nreg_seq;
      END IF;

      -- If this is the first time this UDI has generated a registry_id then
      -- there is nothing to do (last digit obtained above was a 1). Otherwise,
      -- build the registry_id of this UDI's previous entry:
      IF _nreg_seq = 1
      THEN
         NEW.dataset_download_status = 'None';
         NEW.metadata_dl_status = 'None';
         RETURN NEW;
      ELSE
         SELECT LPAD(CAST(_nreg_seq - 1 AS TEXT), 3, '0') INTO _oreg_seq;
         _oreg_id = SUBSTRING(NEW.registry_id FROM 1 FOR 17) || _oreg_seq;
      END IF;
      IF _DEBUG
      THEN
         RAISE NOTICE '_oreg_id: %', _oreg_id;
      END IF;

      -- Get the previous record if there:
      EXECUTE 'SELECT *
               FROM registry
               WHERE registry_id = $1'
         INTO _reg_row
         USING _oreg_id;

      -- If there wasn't one, just let this insertion continue with what was
      -- supplied and worry about it manually:
      IF NOT FOUND
      THEN
         NEW.dataset_download_status = 'None';
         NEW.metadata_dl_status = 'None';
         RETURN NEW;
      END IF;

      IF _DEBUG
      THEN
         RAISE NOTICE 'NEW.dataset_download_status: %',
            NEW.dataset_download_status;
         RAISE NOTICE 'NEW.metadata_dl_status: %',
            NEW.metadata_dl_status;
         RAISE NOTICE 'NEW.metadata_status: %',
            NEW.metadata_status;
         RAISE NOTICE 'NEW.url_data: %',
            NEW.url_data;
         RAISE NOTICE 'NEW.url_metadata: %',
            NEW.url_metadata;
         RAISE NOTICE 'NEW.user_supplied_hash: %',
            NEW.user_supplied_hash;
      END IF;

      -- If we were passed a file URL that is the same as the previous URL,
      -- and the NEW and OLD hashes match or are NULL, assume the dataset file
      -- is the same, unless we were passed a specific dataset download status
      -- other than None. Based on that assumption, copy the old values to the
      -- new record. Otherwise, set the dataset download status to None.
      IF NEW.url_data = _reg_row.url_data AND
         (NEW.user_supplied_hash = _reg_row.user_supplied_hash OR
          _reg_row.user_supplied_hash IS NULL OR
          NEW.user_supplied_hash IS NULL)
      THEN
         IF NEW.dataset_download_status <> 'None' OR
            NEW.dataset_download_status IS NULL
         THEN
            NEW.dataset_download_size   := _reg_row.dataset_download_size;
            NEW.dataset_download_status := _reg_row.dataset_download_status;
            NEW.dataset_filename        := _reg_row.dataset_filename;
            NEW.fs_md5_hash             := _reg_row.fs_md5_hash;
            NEW.fs_sha1_hash            := _reg_row.fs_sha1_hash;
            NEW.fs_sha256_hash          := _reg_row.fs_sha256_hash;
         ELSE
            NEW.dataset_download_status := 'None';
         END IF;
      ELSE
         NEW.dataset_download_status := 'None';
      END IF;

      -- Ditto logic for ithe metadata file.
      IF NEW.url_metadata = _reg_row.url_metadata
      THEN
         IF NEW.metadata_dl_status <> 'None' OR
            NEW.metadata_dl_status IS NULL
         THEN
            NEW.metadata_dl_status := _reg_row.metadata_dl_status;
            NEW.dataset_metadata   := _reg_row.dataset_metadata;
            NEW.metadata_status    := _reg_row.metadata_status;
         ELSE
            NEW.metadata_status    := 'None';
         END IF;
      ELSE
         NEW.metadata_dl_status := 'None';
         NEW.metadata_status    := 'None';
      END IF;

      -- Set the jira_ticket attribute:
      IF NEW.jira_ticket IS NULL OR NEW.jira_ticket = ''
      THEN
         NEW.jira_ticket = _reg_row.jira_ticket;
      END IF;

      -- access_period,
      -- access_period_start,
      -- access_period_weekdays,
      -- access_status,
      -- authentication,
      -- availability_date,
      -- data_server_type,
      -- dataset_abstract,
      -- dataset_download_end_datetime,
      -- dataset_download_error_log,
      -- dataset_download_start_datetime,
      -- dataset_originator,
      -- dataset_poc_email,
      -- dataset_poc_name,
      -- dataset_title,
      -- dataset_udi,
      -- dataset_uuid,
      -- data_source_pull,
      -- doi,
      -- generatedoi,
      -- password,
      -- username

      RETURN NEW;
   END;

$$ LANGUAGE PLPGSQL;

-- Now, create the TRIGGER:
CREATE TRIGGER udf_update_reg_trigger
BEFORE INSERT ON registry
   FOR EACH ROW EXECUTE PROCEDURE udf_update_reg();
