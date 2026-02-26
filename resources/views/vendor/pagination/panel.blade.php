@if (!empty($paginator) and $paginator->hasPages())
    <style>
        .premium-pagination-wrapper {
            display: inline-flex;
            align-items: center;
            background: #fff;
            padding: 8px 12px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #f0f0f0;
        }
        .premium-pagination {
            display: flex;
            align-items: center;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 8px;
        }
        .premium-pagination li {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .premium-pagination li a, 
        .premium-pagination li span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 14px;
            font-weight: 700;
            color: #1f3b64;
            text-decoration: none;
            transition: all 0.2s;
            background: transparent;
            border: none;
        }
        .premium-pagination li a:hover {
            background: #f8faff;
            color: #43d477;
        }
        .premium-pagination li span.active {
            background: #43d477;
            color: #fff;
            box-shadow: 0 4px 10px rgba(67, 212, 119, 0.3);
        }
        .premium-pagination li.disabled {
            opacity: 0.3;
            pointer-events: none;
        }
        .premium-pagination .nav-btn {
            border: 1.5px solid #f0f0f0;
            color: #8c98a4;
        }
        .premium-pagination .nav-btn:hover {
            border-color: #43d477;
            color: #43d477;
        }
        .premium-pagination .nav-btn.active-nav {
            border-color: #43d477;
            color: #43d477;
        }
    </style>

    <nav class="d-flex justify-content-center mt-20">
        <div class="premium-pagination-wrapper">
            <ul class="premium-pagination">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="disabled">
                        <span class="nav-btn"><i data-feather="chevron-left" width="18" height="18"></i></span>
                    </li>
                @else
                    <li>
                        <a href="{{ $paginator->previousPageUrl() }}" class="nav-btn">
                            <i data-feather="chevron-left" width="18" height="18"></i>
                        </a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="disabled"><span>{{ $element }}</span></li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li><span class="active">{{ $page }}</span></li>
                            @else
                                <li><a href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li>
                        <a href="{{ $paginator->nextPageUrl() }}" class="nav-btn">
                            <i data-feather="chevron-right" width="18" height="18"></i>
                        </a>
                    </li>
                @else
                    <li class="disabled">
                        <span class="nav-btn"><i data-feather="chevron-right" width="18" height="18"></i></span>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
@endif
