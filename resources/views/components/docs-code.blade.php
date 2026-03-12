@props(['language' => 'bash'])
<div class="relative my-3">
  <div class="absolute top-3 right-3 text-[10px] font-mono text-slate-400 uppercase">
    {{ $language }}
  </div>
  <pre class="bg-slate-900 text-slate-100 rounded-xl p-4 text-xs font-mono leading-relaxed overflow-x-auto">{{ trim($slot) }}</pre>
</div>
