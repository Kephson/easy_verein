.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _howToStart:

How to start
============
This walkthrough will help you to implement the extension easy_verein at your
TYPO3 site. The installation is covered :ref:`here <installation>`.

.. TODO: add screenshots

.. only:: html

.. contents::
        :local:
        :depth: 1

.. _configureEasyVereinGroupCode:

Configure easyVerein group shortcodes
-------------------------------------
To have the easyVerein groups synchronized with your TYPO3 frontend user groups, please set the easyVerein group code into the field in your TYPO3 user group.

|img-configure-easyverein-groupcode|

.. _howToStartInitialSync:

Do an initial synchronization of members
----------------------------------------
Synchronize your easyVerein members to TYPO3 in a first step to have them in your TYPO3 database.
Go to TYPO3 scheduler and configure a new "execute console commands" jobs and select as command `easyverein:syncfeuser` with the options `--initial=1 --syncAll=1`.
You can also execute this command directly on command line with `typo3 easyverein:syncfeuser --initial=1 --syncAll=1`.

|img-scheduler-initial-sync|

.. caution::
   If the option "Send welcome email after user import" is set in global extension configuration, every user will sent a welcome email after successfull import in TYPO3.

.. _manuallySendWelcomeEmail:

Manually send a welcome email
-----------------------------
If you want to send a welcome email manually to a member, you can do this in his user profile with selecting the option "Send welcome mail to user" and save the record.
After saving the "Last welcome mail sent" field will be updated and the member will get an email.

|img-manually-send-welcome-email|

.. _howToStartInitialSync:

Update scheduler task to synchronize data periodically
------------------------------------------------------
After initial synchronization update your scheduler task to synchronize the data periodically with easyVerein.
Just update the option `--initial=0` to do a regular synchronization.
Choose a repeating timeslot to execute this job, e.g. once a day (84000 seconds).
You can also execute this command directly on command line with `typo3 easyverein:syncfeuser --initial=0 --syncAll=1`

|img-scheduler-recurring-sync|
