<template>
  <main class="space-y-10">
    <section class="space-y-4 text-center">
      <p class="inline-flex items-center gap-2 rounded-full bg-sky-500/10 px-4 py-1 text-sm font-medium text-sky-300">
        TYPO3 Learning Platform
      </p>
      <h1 class="text-4xl font-semibold tracking-tight text-white sm:text-5xl">
        Developer Sandbox
      </h1>
      <p class="mx-auto max-w-2xl text-base text-slate-300">
        This Vite + Vue 3 build acts as the playground for the TYPO3 learning front end. Tailwind CSS is prewired, so you can iterate on search, quizzes, and duel experiences without extra setup.
      </p>
    </section>

    <section class="grid gap-6 sm:grid-cols-2">
      <article class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-white">Core API Targets</h2>
        <ul class="mt-4 space-y-2 text-sm text-slate-300">
          <li>
            <code class="rounded bg-slate-800 px-2 py-1 text-sky-300">GET /api/search?q=typo3</code>
            <span class="block">Vector search backed by pgvector and local Ollama embeddings.</span>
          </li>
          <li>
            <code class="rounded bg-slate-800 px-2 py-1 text-sky-300">POST /api/learn/generate</code>
            <span class="block">Generates practice questions from the syllabus (coming soon).</span>
          </li>
        </ul>
      </article>

      <article class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-white">Next Steps</h2>
        <ol class="mt-4 space-y-2 text-sm text-slate-300 list-decimal list-inside">
          <li>Connect the search endpoint to a composable and display ranked DocChunks.</li>
          <li>Build learning journeys that surface generated questions and track progress.</li>
          <li>Style layouts using Tailwind utility classes and Vue single file components.</li>
        </ol>
      </article>
    </section>
  </main>
</template>

<script setup></script>
