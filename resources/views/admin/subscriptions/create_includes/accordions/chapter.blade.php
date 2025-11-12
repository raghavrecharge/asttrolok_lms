<div class="row mt-10">
    <div class="col-12">
          <div class="panel-collapse text-gray">
     <div class="accordion-content-wrapper mt-15" id="chapterContentAccordion{{ $subscriptionItems->isNotEmpty() ? $subscriptionItems->first()->id : 'record' }}" role="tablist" aria-multiselectable="true">
       @if(!empty($subscriptionItems) && count($subscriptionItems))
         <ul class="draggable-content-lists draggable-lists-chapter-{{ $subscriptionItems->isNotEmpty() ? $subscriptionItems->first()->id : 'record' }}" data-drag-class="draggable-lists-chapter-{{ $subscriptionItems->isNotEmpty() ? $subscriptionItems->first()->id : 'record' }}" data-order-table="subscription_webinar_chapter_items">
            @foreach($subscriptionItems as $subscriptionItem)
                @if($subscriptionItem->type == 'file')
                    @include('admin.subscriptions.create_includes.accordions.file', [
                        'file' => $subscriptionItem,
                        'order' => $subscriptionItem->order,
                        'status' => $subscriptionItem->status,
                        'id' => $subscriptionItem->id
                    ])
                @elseif($subscriptionItem->type == 'quiz')
                    @include('admin.subscriptions.create_includes.accordions.quiz', [
                        'quiz' => $subscriptionItem,
                        'order' => $subscriptionItem->order,
                        'status' => $subscriptionItem->status,
                        'id' => $subscriptionItem->id
                    ])
                @endif
            @endforeach
        </ul>
       @endif
  </div>
</div>
    </div>
</div>