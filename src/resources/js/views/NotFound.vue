<template>
    <div
        class="flex flex-col items-center justify-center min-h-[60vh] space-y-6"
    >
        <p class="text-6xl">404</p>
        <h1 class="text-3xl font-bold">Page Not Found</h1>
        <p class="text-gray-400 text-lg">
            Sorry, we couldn't find the page you're looking for.
        </p>
        <router-link
            to="/"
            class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 px-6 py-3 rounded-lg font-bold transition"
        >
            Back to Home
        </router-link>
    </div>
</template>
