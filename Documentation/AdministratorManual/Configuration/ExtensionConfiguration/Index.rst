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

- Set the API token
- Optional: set a specific API url if not the default one should be used
- Optional: set the default request limit of queries against the API

With default settings it is only needed to set the API token here.

Tab: Typo3
^^^^^^^^^^

- Set the default uid of your frontend users (members) here.
- Set the default storage pid of your frontend users.
- Set page uid of password forget page. It is used in the welcome email to the users.

All these settings should be done.

Tab: Welcomemail
^^^^^^^^^^^^^^^^

- Set specific mail settings for the welcome email which will be sent to the users after import
- Set the sender email address and name
- Set a specific mail subject

All these settings should be done if the default should be overwritten.
