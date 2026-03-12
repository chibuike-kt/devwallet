@props(['headers' => [], 'rows' => []])
<div class="overflow-hidden rounded-xl border border-slate-100 my-3">
  <table class="w-full text-sm">
    <thead class="bg-slate-50">
      <tr>
        @foreach($headers as $header)
        <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">
          {{ $header }}
        </th>
        @endforeach
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      @foreach($rows as $row)
      <tr class="hover:bg-slate-50/50">
        @foreach($row as $i => $cell)
        <td class="px-4 py-3 {{ $i === 0 ? 'font-mono text-xs text-slate-700' : 'text-sm text-slate-600' }}">
          {{ $cell }}
        </td>
        @endforeach
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
