.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt

.. _extensionManager:

Extension configuration globally
--------------------------------

Some general settings can be configured in the Extension Configuration in the administration module.
If you need to configure those, switch to the module "Admin tools" -> "Settings" -> "Extension Configuration", select the extension "**easy_verein**" and open it!

|img-extension-configuration|

There are multiple settings in different tabs.

Tab: Easyverein
^^^^^^^^^^^^^^^

- Set the API token; it is needed to connect with your easyVerein instance
- Optional: set a specific API url if not the default one should be used (default: https://easyverein.com/api/stable)
- Optional: set the default request limit of queries against the API (default: 100)
- Optional: configure allowed fields in easyVerein member model (default: joinDate,membershipNumber)
- Optional: allowed fields in easyVerein contact model (default: bankAccountOwner,bic,iban,methodOfPayment,methodOfPaymentName,sepaDate,street,zip,city,country,salutation,name,familyName,firstName,mobilePhone,privateEmail,privatePhone)

With default settings it is only needed to set the API token here.

Tab: Typo3
^^^^^^^^^^

- Set the default groupd id of your frontend users (members) here (default user group)
- Set the default storage pid of your frontend users (where to store the users)
- Set page uid of password forget page; it is used in the welcome email to the users
- Enable or disable sending of welcome email after user import

All these settings should be done.

|img-extension-configuration-tab-2|

Tab: Welcomemail
^^^^^^^^^^^^^^^^

- Set specific mail settings for the welcome email which will be sent to the users after import
- Set the sender email address and name
- Set a specific mail subject

All these settings should be done if the default should be overwritten.

|img-extension-configuration-tab-3|


.. caution::
   | To have a working mail configuration, please make sure to set
   | $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress']
   | $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName']
   | $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailReplyToAddress']
   | $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailReplyToName']
   | in the global TYPO3 system settings.
