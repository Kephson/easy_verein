.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt

.. _about:

What does it do?
================
This extension connects TYPO3 with the association software easyVerein (https://easyverein.com/).

It allows to synchronize the members within an association with the TYPO3 frontend users to have TYPO3 as your main system to login for your association members.

**Basic features**

- Synchronize members between easyVerein and TYPO3 via scheduler task with the easyVerein API
- Planned: login via TYPO3 and automatically login to easyVerein to have a single-signon feeling for your users


**Pre-requisites**

- TYPO3 CMS in version 11.5 is needed
- TYPO3 scheduler is needed: typo3/cms-scheduler
- TYPO3 frontend login is needed: typo3/cms-felogin
