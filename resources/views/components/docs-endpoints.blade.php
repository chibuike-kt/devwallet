@props(['endpoints' => []])
<div class="space-y-1.5 my-3">
  @foreach($endpoints as [$method, $path, $description])
  <div class="flex items-center gap-3 px-4 py-2.5 border border-slate-100 rounded-xl bg-white hover:bg-slate-50 transition-colors">
    <span class="text-[10px] font-bold px-2 py-0.5 rounded font-mono flex-shrink-0
            {{ $method === 'POST' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
      {{ $method }}
    </span>
    <code class="font-mono text-xs text-slate-700 flex-1">{{ $path }}</code>
    <span class="text-xs text-slate-400">{{ $description }}</span>
  </div>
  @endforeach
</div>
