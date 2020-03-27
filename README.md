# VWI Billing [WIP]
Stripe based billing system for Vesta Web Interface

This plugin is currently under active development and is not able to be installed.

# Features

#### Complete:
 - Create and manage Stripe plans from VWI including free trials.
 - Control public registration of paid and free packages
 - Provide invoices with purchases
 - Complete Stripe currency support (134 Currencies supported)
 - User Billing Dashboard
 - Register free / paid accounts
 
 #### Coming Soon:
 - Cron job to check user status
    - Suspend user if unpaid
    - Cancel plan if account closed
 - Listing page to display available plans / details
 - Change plan after account created
 - Settings page in admin console to control listings page
 - Coupon support
 - Multi tiered pricing for packages (currently only 1 plan per package allowed)
 - Usage based pricing (charge per bandwidth, disk usage, domains used, etc.)
 - Manually add users to plans
 

# Installation / Configuration Process

1. Use the included billing.sql file to create the three new tables, 'billing-config', 'billing-plans', and 'billing-customers' within your existing VWI database. 

   Note: If you used a table prefix other than 'vwi_' you must change the table names in the billing.sql files to your specified prefix.

2. Connect your Stripe account by entering your Secret API Key and Public API Key in the 'sec_key' and 'pub_key' fields of the 'billing-config' table. 

    You may enter a test secret and public API key in these fields to test your installation and plans.

    Alternatively, you may enter a Restricted API key in place of the Secret API Key to restrict access from the VWI endpoint to only the necessary Stripe functions.

    To use a Restricted API Key, create a new key in your Stripe developer console with the following permissions:

    #### Read Access:
     - Invoices
    
    #### Write Access:
     - Customers
     - Plans
     - Products
     - Subscriptions
     - Tokens
    
3. Upload the 'billing' and billing-client' folders to your VWI 'plugins' folder.

4. Activate the plugin by adding `billing,billing-client` to your plugins list.

# Disclaimer

Vesta Web Interface and the VWI-Billing Plugin are licensed under the terms of version 3 of the GNU General Public License as published by the Free Software Foundation.

```
THERE IS NO WARRANTY FOR THE PROGRAM, TO THE EXTENT PERMITTED BY
APPLICABLE LAW.  EXCEPT WHEN OTHERWISE STATED IN WRITING THE COPYRIGHT
HOLDERS AND/OR OTHER PARTIES PROVIDE THE PROGRAM "AS IS" WITHOUT WARRANTY
OF ANY KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING, BUT NOT LIMITED TO,
THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
PURPOSE.  THE ENTIRE RISK AS TO THE QUALITY AND PERFORMANCE OF THE PROGRAM
IS WITH YOU.  SHOULD THE PROGRAM PROVE DEFECTIVE, YOU ASSUME THE COST OF
ALL NECESSARY SERVICING, REPAIR OR CORRECTION.

IN NO EVENT UNLESS REQUIRED BY APPLICABLE LAW OR AGREED TO IN WRITING
WILL ANY COPYRIGHT HOLDER, OR ANY OTHER PARTY WHO MODIFIES AND/OR CONVEYS
THE PROGRAM AS PERMITTED ABOVE, BE LIABLE TO YOU FOR DAMAGES, INCLUDING ANY
GENERAL, SPECIAL, INCIDENTAL OR CONSEQUENTIAL DAMAGES ARISING OUT OF THE
USE OR INABILITY TO USE THE PROGRAM (INCLUDING BUT NOT LIMITED TO LOSS OF
DATA OR DATA BEING RENDERED INACCURATE OR LOSSES SUSTAINED BY YOU OR THIRD
PARTIES OR A FAILURE OF THE PROGRAM TO OPERATE WITH ANY OTHER PROGRAMS),
EVEN IF SUCH HOLDER OR OTHER PARTY HAS BEEN ADVISED OF THE POSSIBILITY OF
SUCH DAMAGES.
```


[Project Home](https://github.com/cdgco/vestawebinterface)

[Plugin List](https://github.com/cdgco/VestaWebInterface/tree/master/plugins)
