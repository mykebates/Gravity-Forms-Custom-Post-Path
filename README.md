Gravity-Forms-Custom-Post-Path
==============================

Post your gravity field values to an additional location such as a CRM like Market or Salesforce.

# This is a very early working version

The idea behind this plugin is to provide an easy way to post your field value out to any other endpoint.

- Make sure the Gravity Forms is installed and activated.
- Install and activate Gravity-Forms-Custom-Post-Path plugin.
- Navigate to existing form in wp-admin.
- In the Advanced tab on the form settings provide the url where we will be posting to.
- Make sure each field you would like to post has the Field Name For Custom Post Path set in the advanced tab(these might need to be specific depending on the endpoint you are posting to).
- Feel free to add hidden fields that may contain data like your service key or lead location, etc.
- Test it out!

## What is the idea behind this plugin?

It's simple really.  More and more I have come across the need to path up a bunch of highly specific code to hook into the Gravity Form submission to intercept the post data in order to send off to CRMs like Marketo, SalesForce, etc.  I wanted an easy way to tell Gravity Forms the location to post to and here are the field names I want you to assign to the fields.

This is a very lose way of being able to accomplish the same task with almost any endpoint no matter what the service is.  As long as they are expecting posted form values you should be able to effortlessly kill two birds with one stone.

### No birds were harmed in creating this plugin.