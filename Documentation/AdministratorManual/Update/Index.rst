.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


Updating
--------
If you update EXT:easy_verein to a newer version, please read this section carefully!

Versioning
^^^^^^^^^^
EXT:easy_verein uses a 3-number versioning scheme: *<major>.<minor>.<patch>*

- Major: Major breaking changes
- Minor: Minor breaking changes
- Patch: No breaking changes

Before an update
^^^^^^^^^^^^^^^^

Before you start the update procedure, please read the changelog of all versions which have been
released in the meantime! You can find those in the manual :ref:`here <changelog>`.

Furthermore it is **always** a good idea to do updates on a dedicated test installation or at least create a database backup.

Upgrade from version 1 to version 2
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Important: only easyVerein API v3.0 or latest will be supported by version 2 of this extension!

Upgrade from version 1 to version 2 should only need some small updates for the extension:

- in easyVerein backend go to "Integrations/easyVerein API" and generate a new API key (old ones you can find in the section "Old API keys (beta)")
- in TYPO3 backend go to  "Settings/Configure extensions" and store the new API key in "Initial easyVerein API bearer token" field
- change the "easyVerein API Uri" to "https://easyverein.com/api/latest" or "https://easyverein.com/api/v3.0" if not set by default
- change "Allowed fields in easyVerein member model" to "join_date,membership_number"
- change "Allowed fields in easyVerein contact model" to "bank_account_owner,bic,iban,method_of_payment,method_of_payment_name,sepa_date,street,zip,city,country,salutation,name,family_name,first_name,mobile_phone,private_email,private_phone"

You're done.
