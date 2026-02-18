
            @foreach($course->chapters as $chapter)

                @if((!empty($chapter->chapterItems) and count($chapter->chapterItems)) or (!empty($chapter->quizzes) and count($chapter->quizzes)))
                    
                                @if(!empty($chapter->chapterItems) and count($chapter->chapterItems))
                                    @foreach($chapter->chapterItems as $chapterItem)
                                          @if($chapterItem->type == \App\Models\WebinarChapterItem::$chapterFile and !empty($chapterItem->file) and $chapterItem->file->status == 'active')
                                            @include('web.default2.remedy.tabs.contents.files' , ['file' => $chapterItem->file, 'accordionParent' => 'chaptersAccordion'])
                                            @endif
                                    @endforeach
                                @endif
                            
                @endif
            @endforeach

