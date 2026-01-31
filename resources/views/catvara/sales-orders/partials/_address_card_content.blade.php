<div class="text-[10px] font-black text-slate-800 truncate">
  {{ $name }}</div>
@if ($address->phone)
  <div class="text-[9px] font-bold text-slate-500 truncate">{{ $address->phone }}</div>
@endif
@if ($address->email)
  <div class="text-[9px] font-bold text-slate-500 truncate">{{ $address->email }}</div>
@endif
<div class="text-[9px] font-bold text-slate-500 mt-1 leading-relaxed">
  {!! $address->render(true) ?: '-' !!}
</div>
