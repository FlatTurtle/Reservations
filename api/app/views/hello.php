<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>FlatTurtle Reservation API</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <link rel="stylesheet" href="./assets/application.css" type="text/css"/>
    <link rel="icon" href="https://flatturtle.com/wp-content/uploads/2013/08/favicon.ico" data="https://flatturtle.com/wp-content/uploads/2013/08/favicon.ico" />
    <script src="./assets/application.js"></script>
    <script src="./assets/jquery-2.0.3.min.js"></script>
    <script type="text/javascript">

    var amenity = {
      'description' : 'Broadband wireless connection in every meeting room.',
      'schema' : {
        '$schema' : 'http://json-schema.org/draft-04/schema#',
        'title' : 'wifi',
        'description' : 'Broadband wireless connection in every meeting room.',
        'type' : 'object', 
        'properties' : {
          'essid' : {
            'description' : 'Service set identifier.',
            'type' : 'string'
          },
          'code' : {
            'description' : 'Authentication code.',
            'type' : 'string'
          },
          'encryption' : {
            'description' : 'Encryption system (e.g. WEP, WPA, WPA2).',
            'type' : 'string'
          }
        },
        'required' : ['essid', 'code']
      }
    };

    var from = new Date();
    from.setFullYear(from.getFullYear() - 1)
    from = from.getFullYear() + "-" + (from.getMonth()+1) + "-" + from.getDate() + " " + from.getHours() + ":" + from.getMinutes();
    var to = new Date();
    to.setFullYear(to.getFullYear() + 1)
    to = to.getFullYear() + "-" + (to.getMonth()+1) + "-" + to.getDate() + " " + to.getHours() + ":" + to.getMinutes();


    var entity = {
      'name' : 'Deep Blue',
      'type' : 'room',
      'body' : {
        'name' : 'Deep Blue',
        'type' : 'room',
        'opening_hours' : [
            {
              'opens' : ['09:00', '13:00'],
              'closes' : ['12:00', '17:00'],
              'dayOfWeek' : 1,
              'validFrom' : from,
              'validThrough' : to
            },
            {
              'opens' : ['09:00', '13:00'],
              'closes' : ['12:00', '17:00'],
              'dayOfWeek' : 2,
              'validFrom' : from,
              'validThrough' : to
            },
            {
              'opens' : ['09:00', '13:00'],
              'closes' : ['12:00', '17:00'],
              'dayOfWeek' : 3,
              'validFrom' : from,
              'validThrough' : to
            },
            {
              'opens' : ['09:00', '13:00'],
              'closes' : ['12:00', '17:00'],
              'dayOfWeek' : 4,
              'validFrom' : from,
              'validThrough' : to
            },
            {
              'opens' : ['09:00', '13:00'],
              'closes' : ['12:00', '17:00'],
              'dayOfWeek' : 5,
              'validFrom' : from,
              'validThrough' : to
            },
            {
              'opens' : ['09:00', '13:00'],
              'closes' : ['12:00', '17:00'],
              'dayOfWeek' : 6,
              'validFrom' : from,
              'validThrough' : to
            },
            {
              'opens' : ['09:00', '13:00'],
              'closes' : ['12:00', '17:00'],
              'dayOfWeek' : 7,
              'validFrom' : from,
              'validThrough' : to
            }
        ],
        'price' : {
          'currency' : 'EUR',
          'hourly' : 5,
          'daily' : 40
        },
        'description' : 'description',
        'location' : {
          'map' : {
            'img' : 'http://foo.bar/map.png',
            'reference' : 'DB'
          },
          'floor' : 1,
          'building_name' : 'main'
        },
        'contact' : 'http://foo.bar/contact.vcf',
        'support' : 'http://foo.bar/support.vcf'
      },
      'amenities' : [
        {
          'type' : 'wifi',
          'body' : {
            'essid' : 'essid',
            'code' : '123456'
          }
        }
      ]
    };

    var from = new Date();
    var to = new Date();
    from.setHours(14);
    to.setHours(16);

    var reservation = {
      'entity' : 'deep_blue',
      'type' : 'room',
      'time' : {
        'from' : from.toISOString(),
        'to' : to.toISOString()
      },
      'subject' : 'meeting',
      'comment' : 'comment',
      'announce' : ['yeri', 'pieter', 'nik', 'quentin']
    };

    $( document ).ready(function() {
      
      $.ajax({
      url: '/freelance/irail/reservations/api/public/test/amenity/test_amenity',
      type: 'PUT',
      data : amenity,
      beforeSend: function (xhr) {
          xhr.setRequestHeader ("Authorization", "Basic "+ btoa('test' + ":" + 'test'));
      }
    })
    .done(function( result ) {
      console.log(result);
    })
    .fail(function( result ) {
      console.log(result);
    });

    $.ajax({
      url: '/freelance/irail/reservations/api/public/test/deep_blue',
      type: 'PUT',
      data : entity,
      beforeSend: function (xhr) {
          xhr.setRequestHeader ("Authorization", "Basic "+ btoa('test' + ":" + 'test'));
      }
    })
    .done(function( result ) {
      console.log(result);
    })
    .fail(function( result ) {
      console.log(result);
    });

    $.ajax({
      url: '/freelance/irail/reservations/api/public/test/reservation',
      type: 'POST',
      data : reservation,
      beforeSend: function (xhr) {
          xhr.setRequestHeader ("Authorization", "Basic "+ btoa('test' + ":" + 'test'));
      }
    })
    .done(function( result ) {
      console.log(result);
    })
    .fail(function( result ) {
      console.log(result);
    });

    $.ajax({
      url: '/freelance/irail/reservations/api/public/test',
      type: 'GET',
      data : reservation,
      beforeSend: function (xhr) {
          xhr.setRequestHeader ("Authorization", "Basic "+ btoa('test' + ":" + 'test'));
      }
    })
    .done(function( result ) {
      console.log(result);
    })
    .fail(function( result ) {
      console.log(result);
    });

    $.ajax({
      url: '/freelance/irail/reservations/api/public/test/deep_blue',
      type: 'GET',
      data : reservation,
      beforeSend: function (xhr) {
          xhr.setRequestHeader ("Authorization", "Basic "+ btoa('test' + ":" + 'test'));
      }
    })
    .done(function( result ) {
      console.log(result);
    })
    .fail(function( result ) {
      console.log(result);
    });

    $.ajax({
      url: '/freelance/irail/reservations/api/public/test/amenity',
      type: 'GET',
      data : reservation,
      beforeSend: function (xhr) {
          xhr.setRequestHeader ("Authorization", "Basic "+ btoa('test' + ":" + 'test'));
      }
    })
    .done(function( result ) {
      console.log(result);
    })
    .fail(function( result ) {
      console.log(result);
    });

    $.ajax({
      url: '/freelance/irail/reservations/api/public/test/amenity/test_amenity',
      type: 'GET',
      data : reservation,
      beforeSend: function (xhr) {
          xhr.setRequestHeader ("Authorization", "Basic "+ btoa('test' + ":" + 'test'));
      }
    })
    .done(function( result ) {
      console.log(result);
    })
    .fail(function( result ) {
      console.log(result);
    });

     $.ajax({
      url: '/freelance/irail/reservations/api/public/test/reservation',
      type: 'GET',
      data : reservation,
      beforeSend: function (xhr) {
          xhr.setRequestHeader ("Authorization", "Basic "+ btoa('test' + ":" + 'test'));
      }
    })
    .done(function( result ) {
      console.log(result);
    })
    .fail(function( result ) {
      console.log(result);
    });

    $.ajax({
      url: '/freelance/irail/reservations/api/public/test/amenity/test_amenity',
      type: 'DELETE',
      data : reservation,
      beforeSend: function (xhr) {
          xhr.setRequestHeader ("Authorization", "Basic "+ btoa('test' + ":" + 'test'));
      }
    })
    .done(function( result ) {
      console.log(result);
    })
    .fail(function( result ) {
      console.log(result);
    });
  });
    

    </script>
  </head>
  <body class="">
    <div id="header" class="wrap">
      <h1 class="logo"><a href="/"><img src="./images/FlatTurtle.png" alt="logo" /></a></h1>
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
        <a href='/api.json'>GET /{customername}</a>
      </dt>
      <dd>Returns a list of links to things that can be reserved.</dd>
      <dd>
<pre class='terminal'>
[{
    "name": "Deep Blue",
    "price": {"amount" : "0.5", "grouping" : "hourly", currency" : "EUR"}, 
    "type": "meetingroom"
    "opening_hours": [
        {
            "opens" : ["09:00", "13:00"],
            "closes" : ["12:00", "17:00"],
            "dayOfWeek" : 1,
            "validFrom" : 1382202015,
            "validThrough" : 1382202015
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
        "http://reservation.{hostname}/{customername}/amenity/wifi" : {
            "label" : "WiFi Deep Blue"
        }, 
        "http://reservation.{hostname}/{customername}/amenity/phone": {
            "label": "phone",
            "number" : "+32 ..."
        },
        "http://reservation.{hostname}/{customername}/amenity/whiteboard" : { 
        }
    }
}]
</pre>
      </dd>

      <dt id='api-put-entity'>
        <a href='/api.json'>PUT /{customername}/{entity_name}</a>
      </dt>
      <dd>Adds a new meeting room. JSON accepted.</dd>
      <dd>
<pre class='terminal'>
Need to define error / success messages format
</pre>
      </dd>

      <dt id='api-get-reservations'>
        <a href='/api/status.json'>GET /{customer_name}/reservation</a>
      </dt>
      <dd>Returns list of reservations made for the current day. Day can be changed with the GET parameter ?day=2013-10-12</dd>
      <dd>
<pre class='terminal'>
[{
"thing" : "http://reservation.{hostname}/{customername}/DB",
"type": "meetingroom",
"time" : {
    "from" : "2013-09-26T12:00Z", //iso8601
    "to"      :  "2013-09-26T14:00Z"
 },   
 "comment" : "Last time I booked a room there was not enough water in the room, can someone please check?",
 "customer" : {
    "mail" : "pieter@flatturtle.com" , "company" : "http://FlatTurtle.com"
  },
 "subject" : "Board meeting",
 "announce" : ["Jan Janssens", "Yeri Tiete"], // For on screen announcements
}]
</pre>
      </dd>
      <dt id='api-post-reservation'>
        <a href='/api/last-message.json'>POST /{customer_name}/reservation</a>
      </dt>
      <dd>Create or update a reservation.Returns 400 if thing is occupied or not open when POST.</dd>
      <dd>
<pre class='terminal'>
Need to define error / success messages format
</pre>
      </dd>
      <dt id='api-get-amenities'>
        <a href='/api/messages.json'>GET /{customer_name}/amenity</a>
      </dt>
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
        <a href='/api/messages.json'>GET /{customer_name}/amenity/red_phone</a>
      </dt>
      <dd>Returns information about a certain amenity.</dd>
      <dd>
<pre class='terminal'>
[
    { 
        "name" : "red_phone",
        "number" : "+32 ..."
    }

]
</pre>
      </dd>

      <dt id='api-put-amenity'>
        <a href='/api/messages.json'>PUT /{customer_name}/amenity/new_amenity</a>
      </dt>
      <dd>Adds a new kind of amenity when authenticated as customer.</dd>
      <dd>
<pre class='terminal'>
Need to define error / success messages format. When creating a new amenity, you define it with a 
json schema.
</pre>
      </dd>

    <dt id='api-put-amenity'>
        <a href='/api/messages.json'>DELETE /{customer_name}/amenity/new_amenity</a>
      </dt>
      <dd>Remove an amenity when authenticated as customer.</dd>
      <dd>
<pre class='terminal'>
Need to define error / success messages format
</pre>
      </dd>
      
    </dl>
  </div>
</div>


    <div id="footer" class="wrap">
      <div id="legal">
        <ul>
          <li><a href="http://flatturtle.com">FlatTurtle Website</a></li>
          <li><a href="mailto:support@flatturtle.com">Support</a></li>
          <li><a href="https://flatturtle.com/contact">Contact</a></li>
          <li><a href="/api">API</a></li>
        </ul>
        <p>© 2013 FlatTurtle bvba. All rights reserved.</p>
      </div>
      
    </div>
  </body>
</html>
<!-- always remember that github loves you dearly -->
