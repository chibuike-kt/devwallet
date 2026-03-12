@props(['url'])
<div class="flex items-center gap-3 bg-slate-900 rounded-xl px-4 py-3 mb-4">
  <span class="text-xs text-slate-400 flex-shrink-0 font-medium">Base URL</span>
  <code class="font-mono text-sm text-emerald-400 flex-1">{{ $url }}</code>
</div>
