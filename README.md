## Opencart QuikWallet Payment Extension

QuikWallet is a payment gateway providing Visa, Master Card, American Express and Netbanking payments.

### Installation steps

Copy all files/folders recursively to opencart installation directory..

Go to Admin Panel, Extensions->Payments and install the QuikWallet gateway extension.

Click on Edit next to QuikWallet and add your Partner ID, Partner Secret and QuikWallet Server URL shared with you from QuikWallet and save the extension.

Make sure that you enable the extension in order for the QuikWallet to be shown as payment option while check out on front end.

Remaining  fields like order status and sort order can be left as default values.


### Sample values
Configuration

Example 

url -  https://uat.quikpay.in/api/partner //API domain specified by QuikWallet for Test/Production
partnerid - 75 //Unique id allotted to each merchant

secret -  gA73CzmjhlqfGsJxP7s811ZVmnl70Jky  //256-bit key that to be stored securely


### Support

For support requests or questions email us on support@livquik.com
