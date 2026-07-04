{{-- Overrides the mll-lab/laravel-graphiql bundled template so GraphiQL
     sends a CSRF token that matches the current session on every
     request. Reading the XSRF-TOKEN cookie fresh per-fetch (rather
     than baking `csrf_token()` into a meta tag at page render) picks
     up token rotations — e.g. after running a `login` mutation,
     Laravel rotates the session token, the browser receives the new
     XSRF-TOKEN cookie on that response, and the next request picks
     it up. The meta-tag approach served the token stale and caused
     419s on every call after the first mutation. --}}
@php
use MLL\GraphiQL\GraphiQLAsset;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GraphiQL</title>
    <style>
        body {
            margin: 0;
            overflow: hidden; /* in Firefox */
        }

        #graphiql {
            height: 100dvh;
        }

        #graphiql-loading {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
        }

        .docExplorerWrap {
            /* Allow scrolling, see https://github.com/graphql/graphiql/issues/3098. */
            overflow: auto !important;
        }
    </style>
    <script src="{{ GraphiQLAsset::reactJS() }}"></script>
    <script src="{{ GraphiQLAsset::reactDOMJS() }}"></script>
    <link rel="stylesheet" href="{{ GraphiQLAsset::graphiQLCSS() }}"/>
    <link rel="stylesheet" href="{{ GraphiQLAsset::pluginExplorerCSS() }}"/>
    <link rel="shortcut icon" href="{{ GraphiQLAsset::favicon() }}"/>
</head>

<body>

<div id="graphiql">
    <div id="graphiql-loading">Loading…</div>
</div>

<script src="{{ GraphiQLAsset::graphiQLJS() }}"></script>
<script src="{{ GraphiQLAsset::pluginExplorerJS() }}"></script>
<script>
    function readXsrfToken() {
        const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]+)/);
        return match ? decodeURIComponent(match[1]) : null;
    }

    const fetcher = GraphiQL.createFetcher({
        url: '{{ $url }}',
        subscriptionUrl: '{{ $subscriptionUrl }}',
        fetch: (input, init = {}) => {
            const headers = new Headers(init.headers || {});
            const token = readXsrfToken();
            if (token && !headers.has('X-XSRF-TOKEN')) {
                headers.set('X-XSRF-TOKEN', token);
            }
            return window.fetch(input, {
                ...init,
                headers,
                credentials: 'same-origin',
            });
        },
    });
    const explorer = GraphiQLPluginExplorer.explorerPlugin();

    // Pre-seeded tab for local dev convenience: one Logout + one Login mutation
    // per seeded role. GraphiQL's operation picker lets the user choose which
    // to run from this single tab. Credentials match
    // backend/database/seeders/UserSeeder.php for the standard dev seed.
    // GraphiQL persists tab state in localStorage so this only applies on first
    // visit; clear `graphiql:tabState` to reset.
    const defaultTabs = [
        {
            query: `# Dev login mutations. Pick an operation from the play-button dropdown.

mutation LoginAppAdmin {
  login(email: "applicationAdministrator@meshresearch.net", password: "adminPassword!@#") {
    id username email roles { name }
  }
}

mutation LoginPubAdmin {
  login(email: "publicationAdministrator@meshresearch.net", password: "publicationadminPassword!@#") {
    id username email roles { name }
  }
}

mutation LoginPubEditor {
  login(email: "publicationEditor@meshresearch.net", password: "editorPassword!@#") {
    id username email roles { name }
  }
}

mutation LoginReviewCoord {
  login(email: "reviewCoordinator@meshresearch.net", password: "coordinatorPassword!@#") {
    id username email roles { name }
  }
}

mutation LoginReviewer {
  login(email: "reviewer@meshresearch.net", password: "reviewerPassword!@#") {
    id username email roles { name }
  }
}

mutation LoginRegularUser {
  login(email: "regularuser@meshresearch.net", password: "regularPassword!@#") {
    id username email roles { name }
  }
}

mutation Logout {
  logout { id username }
}
`,
        },
    ];

    function GraphiQLWithExplorer() {
        return React.createElement(GraphiQL, {
            fetcher,
            plugins: [
                explorer,
            ],
            defaultTabs,
        });
    }

    ReactDOM.render(
        React.createElement(GraphiQLWithExplorer),
        document.getElementById('graphiql'),
    );
</script>

</body>
</html>
