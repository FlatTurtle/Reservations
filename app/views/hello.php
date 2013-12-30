<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>FlatTurtle Reservation API</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <link rel="stylesheet" href="./assets/bootstrap.min.css" type="text/css"/>
    <link rel="stylesheet" href="./assets/application.css" type="text/css"/>
    <link rel="icon" href="https://img.flatturtle.com/favicon/favicon.ico" data="https://img.flatturtle.com/favicon/favicon.ico" />
    <script src="./assets/application.js"></script>
    <script src="./assets/jquery-2.0.3.min.js"></script>

  </head>
  <body class="">
    <div id="header" class="wrap">
      <h1 class="logo"><a href="/"><img src="https://img.flatturtle.com/flatturtle/logo/FlatTurtle.png" alt="logo" /></a></h1>
    </div>

    <div id="main">
  <div id="api" class="wrap">
    <h3>API</h3>
    <p>
      Reservations is an API that allows people to reserve things such as meeting rooms, amenities,
      buildings or whatever you can imagine.
    </p>
    <dl>
      <dt id='api-root'>
        <a href='/'>GET /{clustername}/things</a>
        <span class="label">Accept JSON</span>
      </dt>
      <br />
      <dd>Returns a list of links to things that can be reserved.</dd>
      <dd>
<pre class='terminal'>
[{
    "type" : "room",
    "body" : {
      "name": "Deep Blue",
      "price": {"hourly" : "5", "daily": 50, "currency" : "EUR"}, 
      "type": "room"
      "opening_hours": [
          {
              "opens" : ["09:00", "13:00"],
              "closes" : ["12:00", "17:00"],
              "dayOfWeek" : 1,
              "validFrom" : "2013-09-26T12:00Z",
              "validThrough" : "2013-09-26T12:00Z" 
          }
      ],
      "description" : "Deep Blue is located near the start-up garage.",
      "location" : {
          "map" : {
              "img" : "http://foo.bar/map.png",
              "reference" : "DB"
          },
          "floor" : 1,
          "building_name" : "main"
      },
      "contact" : "http://foo.bar/vcard.vcf",
      "support" : "http://foo.bar/vcard.vcf",
      "amenities" : {
          "http://reservation.{hostname}/{clustername}/amenities/wifi" : {
              "label" : "WiFi Deep Blue"
          }, 
          "http://reservation.{hostname}/{clustername}/amenities/phone": {
              "label": "phone",
              "number" : "+32 ..."
          },
          "http://reservation.{hostname}/{clustername}/amenities/whiteboard" : { 
          }
      }
  }
}]
</pre>
      </dd>

      <dt id='api-put-entity'>
        <a href='/'>PUT /{clustername}/things/{thing_name}</a>
        <span class="label">Accept JSON</span>
        <span class="label label-warning">Auth</span>
      </dt>
      <br />
      <dd>Create or update a room and return the room ins JSON.</dd>
      <dd>
<pre class='terminal'>
{
    "type": "room",
    "body": {
        "name": "room 3",
        "price": {"hourly" : "5", "daily": 50, "currency" : "EUR"},
        "type": "room",
        "opening_hours": [
            {
                "opens": ["09:00", "13:00"],
                "closes": ["12:00", "17:00"],
                "dayOfWeek": 1,
                "validFrom": "2013-09-26T12:00Z",
                "validThrough": "2013-09-26T12:00Z"
            }
        ],
        "description": "DeepBlue is located neart he start-upgarage.",
        "location": {
            "map": {
                "img": "http: //foo.bar/map.png",
                "reference": "DB"
            },
            "floor": 1,
            "building_name": "main"
        },
        "contact": "http: //foo.bar/vcard.vcf",
        "support": "http: //foo.bar/vcard.vcf",
        "amenities": {
            "http: //reservation.{hostname}/{clustername}/amenities/wifi": {
                "label": "WiFiDeepBlue"
            },
            "http: //reservation.{hostname}/{clustername}/amenities/phone": {
                "label": "phone",
                "number": "+32..."
            },
            "http: //reservation.{hostname}/{clustername}/amenities/whiteboard": {}
        }
    }
}
</pre>
      </dd>

      <dt id='api-get-reservations'>
        <a href='/api/status.json'>GET /{clustername}/reservations</a>
        <span class="label">Accept JSON</span>
      </dt>
      <br />
      <dd>Returns list of reservations made for the current day. Day can be changed with the GET parameter ?day=2013-10-12</dd>
      <dd>
<pre class='terminal'>
[{
"thing" : "http://reservation.{hostname}/{clustername}/DB",
"type": "meetingroom",
"time" : {
    "from" : "2013-09-26T12:00Z",
    "to"      :  "2013-09-26T14:00Z"
 },
 "comment" : "Last time I booked a room there was not enough water in the room, can someone please check?",
 "customer" : {
    "email" : "user@domain.com", "company" : "companyname"
  },
 "subject" : "Board meeting",
 "announce" : ["Jan Janssens", "Yeri Tiete"], // For on screen announcements
}]
</pre>
      </dd>
      <dt id='api-post-reservation'>
        <a href='/'>POST /{clustername}/reservations</a>
        <span class="label">Accept JSON</span>
        <span class="label label-warning">Auth</span>
      </dt>
      <br />
      <dd>Create or update a reservation and return it as JSON. Returns 400 if thing is occupied or not open when POST.</dd>
      <dd>
<pre class='terminal'>
{
"thing" : "http://reservation.{hostname}/{clustername}/DB",
"type": "meetingroom",
"time" : {
    "from" : "2013-09-26T12:00Z", //iso8601
    "to"      :  "2013-09-26T14:00Z"
 },
 "customer" : {
    "email" : "user@domain.com", "company" : "companyname"
 },
 "comment" : "Last time I booked a room there was not enough water in the room, can someone please check?",
 "subject" : "Board meeting",
 "announce" : ["Jan Janssens", "Yeri Tiete"], // For on screen announcements
}
</pre>
      </dd>
      <dt id='api-delete-reservation'>
        <a href='/api/last-message.json'>DELETE /{clustername}/reservations/{id}</a>
        <span class="label">Accept JSON</span>
        <span class="label label-warning">Auth</span>
      </dt>
      <br />
      <dd>Cancel a reservation by deleting it.</dd>
      <dd>
<pre class='terminal'>
200 OK
</pre>
      </dd>
      <dt id='api-get-amenities'>
        <a href='/api/messages.json'>GET /{clustername}/amenities</a>
        <span class="label">Accept JSON</span>
      </dt>
      <br />
      <dd>Returns list of available amenities.</dd>
      <dd>
<pre class='terminal'>
[
    { 
        "name" : "wifi",
        "essid" : "deep blue",
        "password" : "passwd",
        "encryption" : "WPA2"
    },
    { 
        "name" : "red_phone",
        "number" : "+32 ..."
    }

]
</pre>
      </dd>

      <dt id='api-get-amenity'>
        <a href='/messages.json'>GET /{clustername}/amenities/{amenity}</a>
        <span class="label">Accept JSON</span>
      </dt>
      <br />
      <dd>Returns information about a certain amenity.</dd>
      <dd>
<pre class='terminal'>
[
    { 
        "description" : "Broadband wireless access point",
        "schema" : {
          "$schema" : http://json-schema.org/draft-04/schema#",
          "title" : "wifi",
          "description" : "Broadband wireless access point",
          "properties" : [
            "essid" : {
              "description" : "The essid of your wifi access point",
              "type" : "string"
            }, 
            ...
          ]

        }
    }

]
</pre>
      </dd>

      <dt id='api-put-amenity'>
        <a href='/messages.json'>PUT /{clustername}/amenities/{amenity}</a>
        <span class="label">Accept JSON</span>
        <span class="label label-warning">Auth</span>
      </dt>
      <br />
      <dd>Create or update an amenity and returns it as JSON.</dd>
      <dd>
<pre class='terminal'>
[
    { 
        "description" : "Broadband wireless access point",
        "schema" : {
          "$schema" : http://json-schema.org/draft-04/schema#",
          "title" : "wifi",
          "description" : "Broadband wireless access point",
          "properties" : [
            "essid" : {
              "description" : "The essid of your wifi access point",
              "type" : "string"
            }, 
            ...
          ]

        }
    }

]

The schema has to be a valid json-schema entity, informations about json-schema
are available here http://json-schema.org/.
</pre>
      </dd>

    <dt id='api-delete-amenity'>
        <a href='/messages.json'>DELETE /{clustername}/amenities/{amenity}</a>
        <span class="label">Accept JSON</span>
        <span class="label label-warning">Auth</span>
      </dt>
      <br />
      <dd>Remove an amenity when authenticated as customer.</dd>
      <dd>
<pre class='terminal'>
200 OK
</pre>
      </dd>


      <dt id='api-get-companies'>
          <a href='/api/status.json'>GET /{clustername}/companies</a>
          <span class="label">Accept JSON</span>
      </dt>
      <br />
      <dd>Returns list of companies registered on the specified cluster.</dd>
      <dd>
        <pre class='terminal'>
[
    {
    "id": "2",
    "cluster_id": "1",
    "name": "company name",
    "domains": "[\"@domain.tld\"]",
    "logo_url": "http://domain.tld/logo.png",
    }
]
        </pre>
      </dd>


      <dt id='api-get-company'>
          <a href='/api/status.json'>GET /{clustername}/companies/{company_name}</a>
          <span class="label">Accept JSON</span>
      </dt>
      <br/>
      <dd>Returns the company linked to that name if it exists in that cluster.</dd>
      <dd>
        <pre class='terminal'>
[
    {
    "id": "2",
    "cluster_id": "1",
    "name": "company name",
    "domains": "[\"@domain.tld\"]",
    "logo_url": "http://domain.tld/logo.png",
    }
]
        </pre>
      </dd>

      <dt id='api-put-company'>
          <a href='/api/status.json'>PUT /{clustername}/companies/{company_name}</a>
          <span class="label">Accept JSON</span>
          <span class="label label-warning">Auth</span>
      </dt>
      <br/>
      <dd>Create or update a company. And return it as json.</dd>
      <dd>
        <pre class='terminal'>
{
  "name" : "new_company2",
  "logo_url" : "http://bizcolostate.files.wordpress.com/2013/08/starbucks-coffee-logo.gif",
  "domains" : ["@cbre.com", "@test.com"] //optional
}
        </pre>
      </dd>

      <dt id='api-delete-company'>
          <a href='/messages.json'>DELETE /{clustername}/companies/{company_name}</a>
          <span class="label">Accept JSON</span>
          <span class="label label-warning">Auth</span>
      </dt>
      <br />
      <dd>Remove a company when authenticated as customer.</dd>
      <dd>
<pre class='terminal'>
200 OK
</pre>
    </dl>
  </div>
</div>


    <div id="footer" class="wrap">
      <div id="legal">
        <ul>
          <li><a href="https://FlatTurtle.com">FlatTurtle website</a></li>
          <li><a href="mailto:help@flatturtle.com">Support</a></li>
          <li><a href="https://FlatTurtle.com/#contact">Contact</a></li>
          <li><a href="/api">API</a></li>
        </ul>
        <p>© 2013 <a href="https://FlatTurtle.com">FlatTurtle</a>. Some rights reserved.</p>
      </div>

    </div>
  </body>
</html>
<!-- always remember that github loves you dearly -->
