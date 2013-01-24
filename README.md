Mall Crawl API technical guide
=========

The Mall Crawl API (MAPI) returns information in the JSON format. This document is a technical specification of the exact nature of this API.

Errors
------
There are two types of errors in MAPI - general errors and server errors:

###General errors
A general error is usually the client's fault. For example, providing invalid input, or trying to access an API function that does not exist, is a general error.

A general error looks like this:

    {
        "error": {
            "text": "The page you requested was not found.",
            "ref": "ref00125"
        }
    }

A general error will usually return with an HTTP error code of 400, except in the case of an unknown API function request, (as in the response above) where the code will be 404.

The `ref` attribute of the `error` object identifies an error. This code can be used to find more information in the server logs. It is safe to show to an end user.

It is not recommended blindly to show the text attribute to the user, because it is aimed at the client's developer and not the user.

###Server errors
A server error is more serious. It is usually my fault. For example, a server error could be failure to connect to the database.

A server error looks like this:

    {
        "server_error": {
            "text": "A Database Error Occurred",
            "ref": "c1359035094",
            "can_retry": true
        }
    }

A server error will return with an HTTP error code of 500. Notice that the object is called `server_error` and not just `error`

The `ref` attribute of the `server_error` object identifies an error. This code can be used to find more information in the server logs. It is safe to show to an end user.

It is not recommended blindly to show the text attribute to the user, because it is aimed at the client's developer and not the user.

The `can_retry` attribute is a wild guess telling the client whether it should try to send the request again, or whether such behaviour is futile.

Users
-----
MAPI identifies users by a Facebook access token given to it by the client. This access token does not need to have anything but the default permissions (unless specified by a function) and is, in general, used for authentication only.

There is no registration required for a Facebook user to use the API for the first time. This is done automatically. Also, not all functions even _require_ an access token. See individual documentation for what is required.

The rest of this guide gives URLs relative to the root of the API, for example, on my testing server, `http://mattsains.dyndns.org/bettermall/malls` is simply `/malls`

The API
=======
The Mall Crawl API uses POST exclusively. 

Malls
-----

###`/malls`
*Input parameters:* `mallid`

Returns information about a mall:
* `mallid` - a unique integer
* `name` - the name of the mall, eg., "Walmer Park"
* `x_coord` This along with `y_coord` are decimal coordinates, eg., -35.24521 
* `y_coord`
* `manager_name` - the name of the manager of the mall
* `bio` - a shortish description of the mall
* `website` - the mall's website URL\*
* `twitter` - the mall's Twitter handle\*
* `facebook` - the mall's Facebook handle\*
* `phone` - the mall's phone number, as an unformatted string
* `email` - the mall's email address\*
* `logo` - a URL to an image logo of the mall
* `map` - a URL to an image map of the mall\*
* `polygon` - a URL to a file describing the stores on the map. Probably XML, but that's up to you!\*

\* these parameters are optional. If they are not available, they will be set as false.

###`/malls/near`
*Input parameters:* `x_coord`, `y_coord`

Returns an array called `malls` of the ten closest malls and their info (as above) sorted by closeness. The coordinates must be in decimal form (-35.12514, 12.12512) and *not* the old format (35°15'21", 12°10'33")

###`/malls/stores`
*Input parameters:* `mallid`

Returns an array with the stores in the mall, like this:
    
    {
        "mallid": 1,
        "stores": [
            {
                "storeid": "2",
                "typeid": "1",
                "typename": "Fashion & Accessories",
                "name": "Ackermans",
                "manager_name": "Someone",
                "email": null,
                "bio": "it's a store, mkay",
                "facebook": "AckermansSA",
                "twitter": "Ackermans_SA",
                "website": "http://www.ackermans.co.za/",
                "phone": "0860900600",
                "categories": [
                    {
                        "categoryid": "1",
                        "categoryname": "Fashion & Accessories"
                    }
                ]
            },
            {
                "storeid": "3",
                "typeid": "1",
                "typename": "Fashion & Accessories",
                "name": "Affinity Collection",
                "manager_name": "Who knows",
                "email": null,
                "bio": "I've never seen these people",
                "facebook": "Affinity-Collections",
                "twitter": null,
                "website": null,
                "phone": "04141414",
                "categories": []
            }
        ]
    }

The fields of stores are roughly the same as malls. You can figure it out.