<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles / Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans antialiased">
    <div class=" min-h-screen bg-gray-100 flex justify-center items-center dark:bg-gray-900">
        <div class="w-full sm:max-w-sm bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg px-6 py-4">
            <h2 class="mb-4 font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Confirm connection of your {{ \JoelButcher\Socialstream\Providers::name($provider) }} account.
            </h2>

            <form method="POST" action="{{ route('oauth.callback.confirm', $provider) }}">
                @csrf
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    @if (config('socialstream.confirmation-prompt'))
                        {{ config('socialstream.confirmation-prompt') }}
                    @else
                        To ensure you are the account owner of this {{ \JoelButcher\Socialstream\Providers::name($provider) }} account,
                        please confirm or deny the request below to link this provider to your account.
                    @endif
                </p>

                <div class="mt-4 flex items-center justify-between">
                    <button type="submit" name="result" value="deny" class="inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        Deny
                    </button>

                    <button type="submit" name="result" value="confirm" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 transition ease-in-out duration-150">
                        Confirm
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
