# Weather Service API, version 1

Every request needs the header X-API-Key.

Base path: /api/v1

## POST /weather/refresh

What it does:

Saves the current temperature reported by the weather source for Iligan City. The reading is stored with the current
server time. Only POST is allowed because this endpoint saves data.

Success response (HTTP 201):

    {
        "status": 201,
        "data": {
            "id": 1,
            "city": "Iligan City",
            "temperatureC": 31.4,
            "fetchedAt": "2026-10-16T15:55:53"
        }
    }

Errors:

- 401 with code `unauthorized` — the X-API-Key header is missing or wrong.
- 500 with code `server_error` — the reading could not be saved.
- 502 with code `upstream_error` — the weather source could not be reached or returned no temperature.

## GET /weather/logs

What it does:

Returns the saved readings, newest first. Only GET is allowed because this endpoint only reads data.

Options:

- `limit` (optional, default 10): a whole number from 1 to 50.
- `format` (optional, default json): json or xml.

Success response (HTTP 200, format json):

    {
        "status": 200,
        "data": [
            {
                "id": 2,
                "city": "Iligan City",
                "temperatureC": 30.1,
                "fetchedAt": "2026-10-16T15:40:00"
            },
            {
                "id": 1,
                "city": "Iligan City",
                "temperatureC": 31.4,
                "fetchedAt": "2026-10-16T15:55:53"
            }
        ]
    }

Success response (HTTP 200, format xml):

    <?xml version="1.0" encoding="UTF-8"?>
    <weatherReport>
        <reading>
            <id>2</id>
            <city>Iligan City</city>
            <temperatureC>30.1</temperatureC>
            <fetchedAt>2026-10-16T15:40:00</fetchedAt>
        </reading>
        <reading>
            <id>1</id>
            <city>Iligan City</city>
            <temperatureC>31.4</temperatureC>
            <fetchedAt>2026-10-16T15:55:53</fetchedAt>
        </reading>
    </weatherReport>

The XML is checked against app/Schemas/weather-report.xsd before it is sent.

Errors:

- 401 with code `unauthorized` — the X-API-Key header is missing or wrong.
- 422 with code `invalid_limit` — `limit` is not a whole number from 1 to 50.
- 422 with code `invalid_format` — `format` is not json or xml.

## GET /weather/logs/{id}

What it does:

Returns one saved reading by its id. Only GET is allowed because this endpoint only reads data.

Success response (HTTP 200):

    {
        "status": 200,
        "data": {
            "id": 1,
            "city": "Iligan City",
            "temperatureC": 31.4,
            "fetchedAt": "2026-10-16T15:55:53"
        }
    }

Errors:

- 401 with code `unauthorized` — the X-API-Key header is missing or wrong.
- 404 with code `not_found` — there is no reading with that id.
