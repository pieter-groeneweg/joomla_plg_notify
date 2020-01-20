# notify
notification plugin allowing simple workflow on creating content
Notify sends messages on article creation or existing article editing in front end (site) and back end (admin). Messages are sent to:

- category owners (aka the "created by" user)
- the user group(s) selected in the plugin parameters

Messaging uses core messages functionality. User(s) receive an email a message has been sent. The email just contains the link to the message component list (com_messages) in the back end. User can open the message and see the url to the added or changed article. The url is admin link.

 
"Don't shoot the messenger!"

This plugin just sends out the message. Use core ACL to create your workflow. ie:

- create/modify a group that is allowed to only create and edit own but not publish (and preferrably not delete of course ;) )
- allow the owner(s) of categories to edit state
- when a user in the authors group creates an article, a message is sent to category owner
- category gets notified and can open the article for edition, changes and to change the status
- category owner can also reply through com_messenger
- ...

though a few years old still working :)

It needs some additions... ie. when working with admin folder protection that adds a URL query param, the link in the message will 404.

