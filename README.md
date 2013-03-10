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

Here is an example:

    {
        "mallid": 1,
        "name": "Walmer Park",
        "x_coord": "-33.980316",
        "y_coord": "25.557826",
        "manager_name": "Me",
        "bio": "So and so",
        "website": false,
        "twitter": false,
        "facebook": false,
        "phone": "08124512",
        "email": false,
        "logo": "http://mattsains.dyndns.org/bettermall/assets/malls/x.jpg",
        "map": "http://mattsains.dyndns.org/bettermall/assets/malls/",
        "polygons": "http://mattsains.dyndns.org/bettermall/assets/malls/"
    }

###`/malls/near`
*Input parameters:* `x_coord`, `y_coord`

Returns an array of the ten closest malls and their info (as above) sorted by closeness. The input coordinates must be in decimal form (-35.12514, 12.12512) and *not* the old format (35°15'21", 12°10'33")

Here is an example:

    [
        {
            "mallid": "2",
            "name": "Greenacres",
            "x_coord": "-33.949198",
            "y_coord": "25.576901",
            "manager_name": "h",
            "bio": "h",
            "website": false,
            "twitter": false,
            "facebook": false,
            "phone": "1",
            "email": false,
            "logo": "http://mattsains.dyndns.org/bettermall/assets/malls/h.png",
            "map": "http://mattsains.dyndns.org/bettermall/assets/malls/",
            "polygons": "http://mattsains.dyndns.org/bettermall/assets/malls/"
        },
        {
            "mallid": "1",
            "name": "Walmer Park",
            "x_coord": "-33.980316",
            "y_coord": "25.557826",
            "manager_name": "Me",
            "bio": "So and so",
            "website": false,
            "twitter": false,
            "facebook": false,
            "phone": "08124512",
            "email": false,
            "logo": "http://mattsains.dyndns.org/bettermall/assets/malls/x.jpg",
            "map": "http://mattsains.dyndns.org/bettermall/assets/malls/",
            "polygons": "http://mattsains.dyndns.org/bettermall/assets/malls/"
        }
    ]
    
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
                "logo": "http://mattsains.dyndns.org/bettermall/assets/stores/1/logo.png",
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
                    },
                    {
                        "categoryid": "2",
                        "categoryname": "Some other category"
                    }
                ]
            },
            {
                "storeid": "3",
                "typeid": "1",
                "typename": "Fashion & Accessories",
                "name": "Affinity Collection",
                "logo": false,
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

###`/malls/add`
*Input parameters:* `mallid`,`access_token`

Adds the mall to the user's favourites(?) list. Returns the mallid just for acknowledgement

###`/malls/remove`
*Input parameters:* `mallid`,`access_token`

Removes the mall from the user's favourites(?) list. Returns the mallid just for acknowledgement

Stores
------

###`/stores/`
*Input parameters:* `storeid`

Returns information about a store. Here's an example:

    {
        "storeid": "2",
        "mallid": "1",
        "typeid": "1",
        "type_name": "Fashion & Accessories",
        "name": "Ackermans",
        "logo": "http://mattsains.dyndns.org/bettermall/assets/stores/1/logo.png",
        "manager_name": "Someone",
        "bio": "it's a store, mkay",
        "website": "http://www.ackermans.co.za/",
        "twitter": "Ackermans_SA",
        "facebook": "AckermansSA",
        "phone": "0860900600",
        "email": false,
        "categories": [
            {
                "categoryid": "1",
                "categoryname": "Fashion & Accessories"
            }
        ]
    }

###`/stores/add`
*Input parameters:* `storeid`, `access_token`

Adds a store to the user's star(?) list. Returns the storeid as confirmation

###`/stores/remove`
*Input parameters:* `storeid`, `access_token`

Removes a store from the user's star(?) list. Returns the storeid as confirmation

###`/stores/images/`
*Input parameters:* `storeid`,`access_token`

Returns an array of images attached to the store. Notice that an access_token IS required (because the API queries facebook for the details of the authors of these images)

Here's an example:

    [
        {
            "image": "http://mattsains.dyndns.org/bettermall/assets/stores/gf",
            "thumb": "http://mattsains.dyndns.org/bettermall/assets/stores/",
            "timestamp": "1358963072",
            "userid": "1",
            "username": false,
            "name": false
        },
        {
            "image": "http://mattsains.dyndns.org/bettermall/assets/stores/t",
            "thumb": "http://mattsains.dyndns.org/bettermall/assets/stores/",
            "timestamp": "1358861219",
            "userid": "603356577",
            "username": "matt.sainsbury.31",
            "name": "Matt Sainsbury"
        }
    ]
    
Notice how the first image has the username and name set to false. When the server does not have the username or name, your client must be able to handle this, by not displaying this information.

###`/stores/images/add`
*Input parameters:* `access_token`,`image`,`storeid` where `image` is a image uploaded HTTP form style

This function accepts an image upload, and associates it with the current user, and the storeid given. This function will return a string URL to the uploaded image in JSON.

Caution: File names are not preserved, so do not assume that the file name will be the same once uploaded.

Users
-----
###`/users/login`
*Input parameters:* `access_token`

This function can be used to make sure you have a legitimate login. If there is a problem, an `error` will be returned. If everything is dandy, some useful information about this user is returned:

    {
        "userid": "1467084245",
        "name": "Matthew Sainsbury",
        "uname": "mattsains",
        "photo": "https://graph.facebook.com/mattsains/picture",
        "noupload": false,
        "malls": [
            {
                "mallid": "1",
                "name": "Walmer Park",
                "x_coord": "-33.980316",
                "y_coord": "25.557826",
                "manager_name": "Me",
                "bio": "So and so",
                "website": false,
                "twitter": false,
                "facebook": false,
                "phone": "08124512",
                "email": false,
                "logo": "http://mattsains.dyndns.org/bettermall/assets/malls/x.jpg",
                "map": "http://mattsains.dyndns.org/bettermall/assets/malls/",
                "polygons": "http://mattsains.dyndns.org/bettermall/assets/malls/"
            },
            {
                "mallid": "2",
                "name": "Greenacres",
                "x_coord": "-33.949198",
                "y_coord": "25.576901",
                "manager_name": "h",
                "bio": "h",
                "website": false,
                "twitter": false,
                "facebook": false,
                "phone": "1",
                "email": false,
                "logo": "http://mattsains.dyndns.org/bettermall/assets/malls/h.png",
                "map": "http://mattsains.dyndns.org/bettermall/assets/malls/",
                "polygons": "http://mattsains.dyndns.org/bettermall/assets/malls/"
            }
        ]
    }

###`/users/stores`
*Input parameters:* `access_token`

This function returns all the malls the user has starred:
    
        [
            {
                "storeid": "2",
                "mallid": "1",
                "typeid": "1",
                "type_name": "Fashion & Accessories",
                "name": "Ackermans",
                "logo": "http://mattsains.dyndns.org/bettermall/assets/stores/1/logo.png",
                "manager_name": "Someone",
                "bio": "it's a store, mkay",
                "website": "http://www.ackermans.co.za/",
                "twitter": "Ackermans_SA",
                "facebook": "AckermansSA",
                "phone": "0860900600",
                "email": false
            },
            }
                "storeid": "1"
                (and so on
            }
        ]

Stats
----

MAPI can do some stats which owners might find interesting. Here's how you can make MAPI log data.

For each view of a mall or store, call the following url

###`/stats/[mall|store]`
*Input parameters:* `mallid` or `storeid`

This function will log the view and return the id you gave it, as confirmation.