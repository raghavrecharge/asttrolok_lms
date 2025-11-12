<div class="tab-pane mt-3 fade" id="purchased_courses" role="tabpanel" aria-labelledby="purchased_courses-tab">
    <div class="row">

        @can('admin_enrollment_add_student_to_items')
        <style>
            .accessc{
                display:none;
            }
            .accessc1{
                display:none;
            }
            .accessc2{
                display:none;
            }
        </style>
            <div class="col-12 col-md-6">
                <h5 class="section-title after-line">{{ trans('update.add_student_to_course') }}</h5>

                <form action="{{ getAdminPanelUrl() }}/enrollments/store" method="Post">
                     <!--@csrf-->

                    <input type="hidden" id='uids' name="user_id" value="{{ $user->id }}">

                    <div class="form-group">
                        <label class="input-label">{{trans('admin/main.class')}}</label>
                        <select name="webinar_id" class="form-control search-webinar-select2"
                                data-placeholder="{{trans('panel.choose_webinar')}}" onchange="access_course(this);">
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                 
                   
                   
                    
                    <div class="form-group accessc">
                      <div class="control-label">Course Access Type</div>
                      <div class="custom-switches-stacked mt-2">
                        <label class="custom-switch">
                          <input type="radio" name="option" value="1" class="custom-switch-input full" onchange="fuul_access_course();">
                          <span class="custom-switch-indicator"></span>
                          <span class="custom-switch-description">Full Access</span>
                        </label>
                        <label class="custom-switch">
                          <input type="radio" name="option" value="2" class="custom-switch-input insta" onchange="installmentget();">
                          <span class="custom-switch-indicator"></span>
                          <span class="custom-switch-description">Installment</span>
                        </label>
                        
                      </div>
                    </div>
                    <div class="form-group accessc1">
                      <div class="control-label">Please Select Instalment</div>
                      <div class="custom-switches-stacked mt-2 instaltitle">
                        
                       
                        
                      </div>
                    </div>
                    <div class="form-group accessc2">
                      <div class="control-label">Please Select Instalment steps</div>
                      <div class="custom-switches-stacked mt-2 instaltitlestep">
                        
                       
                        
                      </div>
                    </div>

                    <div class=" mt-4">
                        <!--<button type="submit" class=" btn btn-primary">{{ trans('admin/main.submit') }}</button>-->
                        <button type="button" class="js-save-manual-add btn btn-primary">{{ trans('admin/main.submit') }}</button>
                    </div>
                </form>
                
                @php
                $salewebid = '';
                $saleinstaid = '';
                @endphp
                   @foreach($sales as $sale)
                    
                 
                    @php 
                    // print_r($sale->installment_payment_id );
                    if($sale->webinar_id){
                    $salewebid .= '~'.$sale->webinar_id ;
                    }
                    
                    
                    @endphp
                    @endforeach
                   
                   
                   
                   
                   
                  
                   
                    
                    
                    
                
            </div>
            <script>
            var webid=0;
            // sales= '{{json_encode($sales)}}';
                   
            //     //   for (sale in sales){
            //         //   console.log(sales[sale]);
            //           console.log(sales);
            //     //   }
                function access_course(vlu){
                    if(vlu.value){
                        $('.accessc').css("display", "block");
                   webid=vlu.value;
                   
                   webid2='{{$salewebid}}';
                   webid3=webid2.split("~");
                   if(webid3.includes(webid)){
                       $('.full'). prop("checked" , true);
                   }
                   
                   instaid2='{{$percent_webinar_id}}';
                   instaid3=instaid2.split("~");
                   if(instaid3.includes(vlu.value)){
                       $('.insta'). prop("checked" , true);
                       installmentget();
                   }
                   console.log(instaid3);
                   
                    }else{
                       $(".accessc").css("display", "none"); 
                       $(".accessc1").css("display", "none"); 
                       $(".accessc2").css("display", "none"); 
                    }
                }
                  function fuul_access_course(){
                    $(".accessc2").css("display", "none"); 
                       $(".accessc1").css("display", "none"); 
                    
                }
                function installmentget(){
                    
                   
                 $.ajax({
                url: '/course/learning1/'+webid,
                type: 'get',
                cache: false,
                timeout: 30000,
                data:{ uid: '{{ $user->id }}' },
                success: function (data) {
                    // ale?rt(data);
                    $('.accessc1').css("display", "block");
                     $('.instaltitle').html(data); 
                    $('input[name="installmenttitles"]:checked').each(function() {
                        // alert(this.value);
   installmentgetstep(this);
});
                    
                      
                    
                    
                }
            });
                }
                  function installmentgetstep(id){
                    // alert(webid);
                   
                 $.ajax({
                url: '/course/learning2/'+webid,
                type: 'get',
                cache: false,
                timeout: 30000,
                data:{ uid: '{{ $user->id }}',instid:id.value },
                success: function (data) {
                    //  alert(data);
                   $('.accessc2').css("display", "block");
                    $('.instaltitlestep').html(data);
                    
                         
                    
                }
            });
                }
                  
                
            </script>
        @endcan

        <div class="col-12">
            <div class="mt-5">
                <h5 class="section-title after-line">{{ trans('update.manual_added') }}</h5>

                <div class="table-responsive mt-3">
                    <table class="table table-striped table-md">
                        <tr>
                            <th>{{ trans('admin/main.class') }}</th>
                            <th>{{ trans('admin/main.type') }}</th>
                            <th>{{ trans('admin/main.price') }}</th>
                            <th>{{ trans('admin/main.instructor') }}</th>
                            <th class="text-center">{{ trans('update.added_date') }}</th>
                            <th class="text-right">{{ trans('admin/main.actions') }}</th>
                        </tr>

                        @if(!empty($manualAddedClasses))
                            @foreach($manualAddedClasses as $manualAddedClass)

                                <tr>
                                    <td width="25%">
                                        <a href="{{ !empty($manualAddedClass->webinar) ? $manualAddedClass->webinar->getUrl() : '#1' }}" target="_blank" class="">{{ !empty($manualAddedClass->webinar) ? $manualAddedClass->webinar->title : trans('update.deleted_item') }}</a>
                                    </td>

                                    <td>
                                        @if(!empty($manualAddedClass->webinar))
                                            {{ trans('admin/main.'.$manualAddedClass->webinar->type) }}
                                        @endif
                                    </td>

                                    <td>
                                        @if(!empty($manualAddedClass->webinar))
                                            {{ !empty($manualAddedClass->webinar->price) ? handlePrice($manualAddedClass->webinar->price) : '-' }}
                                        @else
                                            {{ !empty($manualAddedClass->amount) ? handlePrice($manualAddedClass->amount) : '-' }}
                                        @endif
                                    </td>

                                    <td width="25%">
                                        @if(!empty($manualAddedClass->webinar))
                                            <p>{{ $manualAddedClass->webinar->creator->full_name  }}</p>
                                        @else
                                            <p>{{ $manualAddedClass->seller->full_name  }}</p>
                                        @endif
                                    </td>

                                    <td class="text-center">{{ dateTimeFormat($manualAddedClass->created_at,'j M Y | H:i') }}</td>
                                    <td class="text-right">
                                        @can('admin_enrollment_block_access')
                                            @include('admin.includes.delete_button',[
                                                    'url' => getAdminPanelUrl().'/enrollments/'. $manualAddedClass->id .'/block-access',
                                                    'tooltip' => trans('update.block_access'),
                                                    'btnIcon' => 'fa-times-circle'
                                                ])
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </table>
                    <p class="font-12 text-gray mt-1 mb-0">{{ trans('update.manual_add_hint') }}</p>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="mt-5">
                <h5 class="section-title after-line">Installment Classes</h5>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
                <div class="table-responsive mt-3">
                    <table class="table table-striped table-md">
                        <tr>
                            <th>{{ trans('admin/main.class') }}</th>
                            <th>{{ trans('admin/main.type') }}</th>
                            <th>{{ trans('admin/main.price') }}</th>
                            <th>{{ trans('admin/main.instructor') }}</th>
                            <th class="text-center">{{ trans('update.added_date') }}</th>
                            <th class="text-right">Block Access</th>
                        </tr>
                        @php
                        $count=0;
                        $string1=[];
                        @endphp
                        
                        @if(!empty($installmentClasses))
                            @foreach($installmentClasses as $installmentClasse1)
                            
                            

                                @php
                                $webid1=$installmentClasse1->installment->installmentorder->webinar->id;
                                if(!in_array($webid1, $string1)){
                                array_push($string1,$webid1);
                                @endphp 
                                    
                                    <tr>
                                    <td width="25%">
                                        <a href="{{ !empty($installmentClasse1->installment->installmentorder->webinar) ? $installmentClasse1->installment->installmentorder->webinar->getUrl() : '#1' }}" target="_blank" class="">{{ !empty($installmentClasse1->installment->installmentorder->webinar) ? $installmentClasse1->installment->installmentorder->webinar->title : trans('update.deleted_item') }}</a>
                                    </td>
                                    <td>
                                        @if(!empty($installmentClasse1->installment->installmentorder->webinar))
                                            {{ trans('admin/main.'.$installmentClasse1->installment->installmentorder->webinar->type) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($installmentClasse1->installment->installmentorder->webinar))
                                            {{ !empty($installmentClasse1->installment->installmentorder->webinar->price) ? handlePrice($installmentClasse1->installment->installmentorder->webinar->price) : '-' }}
                                        @else
                                            {{ !empty($installmentClasse1->installment->installmentorder->amount) ? handlePrice($installmentClasse1->installment->installmentorder->amount) : '-' }}
                                        @endif
                                    </td>

                                    <td width="25%">
                                        @if(!empty($installmentClasse1->installment->installmentorder->webinar))
                                            <p>{{ $installmentClasse1->installment->installmentorder->webinar->creator->full_name  }}</p>
                                        @else
                                            <p>{{ $installmentClasse1->installment->installmentorder->seller->full_name  }}</p>
                                        @endif
                                    </td>

                                    <td class="text-center">{{ dateTimeFormat($installmentClasse1->installment->installmentorder->created_at,'j M Y | H:i') }}</td>
                                    
                                    <td width="200" class="text-right">
                                        <div class="btn-group dropdown table-actions ">
                                            <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu text-left webinars-lists-dropdown " x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(-5px, 14px, 0px);">
                                               <script>
                                                   var oldvar=0;
                                               </script>
                                                @php
                                                $count11=0;
                                                $instalcount=count($installmentClasses);
                                                    foreach($installmentClasses as $installmentClasse2){
                                
                                                        $webid2=$installmentClasse2->installment->installmentorder->webinar->id;
                                                        if($webid1==$webid2){
                                                        
                                                        $installmentClasses3 = [$webid2 => $installmentClasse2->installment];  
                                                        $count++;
                                                        $count11++;
                                                        
                                                @endphp
                                                      
                                                      
                                                <a href="/admin/users/{{ $user->id }}/editinsta/{{$installmentClasses3[$webid2]->id}}" id="instalmentdelete{{$installmentClasses3[$webid2]->id}}" class="d-flex align-items-center text-dark text-decoration-none btn-transparent btn-sm text-primary mt-1 ">
                                                    <span class="ml-2">&#8377; {{$installmentClasses3[$webid2]->amount}} Installment</span> 
                                                    <i class="fa fa-times ml-2"></i>
                                                </a>
                                               
                                               
                                                
                                                 <script>
                                                 var oldid=oldvar;
                                                
                                                 var newid={{$installmentClasses3[$webid2]->id}}
                                                 oldurl='#';
                                                 newurl="/admin/users/{{ $user->id }}/editinsta/{{$installmentClasses3[$webid2]->id}}";
                                                
                                                 
                                                //  var httpUrl = $("#instalmentdelete"+oldid).attr("href"); // Get current url
                                                 console.log("#instalmentdelete"+oldid);
            // var httpsUrl = httpUrl.replace("http://", "https://"); // Create new url
            // $(this).attr("href", httpsUrl);
            
                                                           $("#instalmentdelete"+oldid).attr("href", oldurl);
                                                           $("#instalmentdelete"+newid).attr("href", newurl);
                                                           oldvar= {{$installmentClasses3[$webid2]->id}}
                                                       </script>
                                          
                                                @php
                                
                                                        // print_r($installmentClasses3[$webid2]->amount);
                                    
                                                        }
                                                    }
                                                @endphp
                        
                                                                 
                                                    
                                            </div>
                                        </div>
                                    </td>
                                    
                                    
                                    <!--<td class="text-right" onclick="viewfile()">-->
                                       
                                            
                                    <!--            <button>Edit Access</button>   -->
                                        
                                    <!--</td>-->

                                </tr>
                                    
                                @php
                                    
                                   
                                        
                                    
                                    //print_r($webid1);
                                    
                           
                                }
                                   
                               @endphp
                               
                               
                               
                               
                               
                                
                            @endforeach
                            @php
                                    
                                    
                                  // for($installmentClasses as $installmentClasse2)
                                   
                                   //print_r($installmentClasses3[$installmentClasse1->installment->installmentorder->webinar->id]->id);
                               @endphp
                        @endif
                    </table>
                    <p class="font-12 text-gray mt-1 mb-0">{{ trans('update.manual_add_hint') }}</p>
                </div>
            </div>
        </div>


        <div class="col-12">
            <div class="mt-5">
                <h5 class="section-title after-line">{{ trans('update.manual_disabled') }}</h5>

                <div class="table-responsive mt-3">
                    <table class="table table-striped table-md">
                        <tr>
                            <th>{{ trans('admin/main.class') }}</th>
                            <th>{{ trans('admin/main.type') }}</th>
                            <th>{{ trans('admin/main.price') }}</th>
                            <th>{{ trans('admin/main.instructor') }}</th>
                            <th class="text-right">{{ trans('admin/main.actions') }}</th>
                        </tr>

                        @if(!empty($manualDisabledClasses))
                            @foreach($manualDisabledClasses as $manualDisabledClass)

                                <tr>
                                    <td width="25%">
                                        <a href="{{ !empty($manualDisabledClass->webinar) ? $manualDisabledClass->webinar->getUrl() : '#1' }}" target="_blank" class="">{{ !empty($manualDisabledClass->webinar) ? $manualDisabledClass->webinar->title : trans('update.deleted_item') }}</a>
                                    </td>

                                    <td>
                                        @if(!empty($manualDisabledClass->webinar))
                                            {{ trans('admin/main.'.$manualDisabledClass->webinar->type) }}
                                        @endif
                                    </td>

                                    <td>
                                        @if(!empty($manualDisabledClass->webinar))
                                            {{ !empty($manualDisabledClass->webinar->price) ? handlePrice($manualDisabledClass->webinar->price) : '-' }}
                                        @else
                                            {{ !empty($manualDisabledClass->amount) ? handlePrice($manualDisabledClass->amount) : '-' }}
                                        @endif
                                    </td>

                                    <td width="25%">
                                        @if(!empty($manualDisabledClass->webinar))
                                            <p>{{ $manualDisabledClass->webinar->creator->full_name  }}</p>
                                        @else
                                            <p>{{ $manualDisabledClass->seller->full_name  }}</p>
                                        @endif
                                    </td>

                                    <td class="text-right">
                                        @can('admin_enrollment_block_access')
                                            @include('admin.includes.delete_button',[
                                                    'url' => getAdminPanelUrl().'/enrollments/'. $manualDisabledClass->id .'/enable-access',
                                                    'tooltip' => trans('update.enable-student-access'),
                                                ])
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </table>
                    <p class="font-12 text-gray mt-1 mb-0">{{ trans('update.manual_remove_hint') }}</p>
                </div>
            </div>
        </div>
<style>
    .progress-bar {
        width: 100%;
        height: 8px;
        background-color: #e0e0e0;
        border-radius: 4px;
    }

    progress::-webkit-progress-bar {
        background-color: #e0e0e0;
        border-radius: 4px;
    }

    progress::-webkit-progress-value {
        background-color: #007bff; /* Blue color for progress */
        border-radius: 4px;
    }

    progress::-moz-progress-bar {
        background-color: #007bff; /* Blue color for Firefox */
        border-radius: 4px;
    }
</style>

<script>
    function showProgressBar() {
        // Logic to update the progress bar dynamically can go here
    }
</script>



        <div class="col-12">
            <div class="mt-5">
                <h5 class="section-title after-line">{{ trans('panel.purchased') }}</h5>

                <div class="table-responsive mt-3">
                    <table class="table table-striped table-md">
                        <tr>
                            <th>{{ trans('admin/main.class') }}</th>
                            <th>Progress Bar %</th>
                            <th>{{ trans('admin/main.type') }}</th>
                            <th>{{ trans('admin/main.price') }}</th>
                            <th>{{ trans('admin/main.instructor') }}</th>
                            <th class="text-center">{{ trans('panel.purchase_date') }}</th>
                            <th>{{ trans('admin/main.actions') }}</th>
                        </tr>

                        @if(!empty($purchasedClasses))
                            @foreach($purchasedClasses as $purchasedClass)

                                <tr>
                                    <td width="25%">
                                        <a href="{{ !empty($purchasedClass->webinar) ? $purchasedClass->webinar->getUrl() : '#1' }}" target="_blank" class="">{{ !empty($purchasedClass->webinar) ? $purchasedClass->webinar->title : trans('update.deleted_item') }}</a>
                                    </td>
                                      <td>
                                          
                                        @php
                                            $Progress = 0;
                                            $totalVideos =0;
                                           $totalChapter = \App\Models\WebinarChapter::where('webinar_chapters.webinar_id', (int) $purchasedClass->webinar->id)->where('status', 'active')->get();
                                           if($totalChapter){
                                              foreach($totalChapter as $value){
                                              $totalItem = \App\Models\WebinarChapterItem::where('chapter_id', (int) $value->id)
                                               ->where('type', 'file')
                                                ->count();
                                                $totalVideos +=$totalItem;
                                              }
                                          }
                                          
                                          $watchedVideos = \App\Models\CourseProgress::where('webinar_id', (int) $purchasedClass->webinar->id)
                                            ->where('user_id',(int) $user->id)  
                                            ->sum('watch_percentage');
                                         
                                        @endphp
                                        
                                        @if($totalVideos)
                                            @php
                                                $Progress = (int) ($watchedVideos/ $totalVideos);
                                                
                                            @endphp
                                        @endif
                                  <a href="{{ url("/admin/users/{$user->id}/{$purchasedClass->webinar->slug}/courseprogress") }}" target="_blank" class="">
                                       <div class="mt-20">
                                        <label for="videoProgress" class="font-16 text-gray">Progress</label>
                                        <progress id="videoProgress" value="{{ $Progress }}" max="100" class="progress-bar"></progress>
                                        <span id="progressValue" class="font-15 text-gray">{{ $Progress }}%</span> 
                                    </div>
                                    </a>
                                    </td>
                                    <td>
                                        @if(!empty($purchasedClass->webinar))
                                            {{ trans('admin/main.'.$purchasedClass->webinar->type) }}
                                        @endif
                                    </td>

                                    <td>
                                        @if(!empty($purchasedClass->webinar))
                                            {{ !empty($purchasedClass->webinar->price) ? handlePrice($purchasedClass->webinar->price) : '-' }}
                                        @else
                                            {{ !empty($purchasedClass->amount) ? handlePrice($purchasedClass->amount) : '-' }}
                                        @endif
                                    </td>

                                    <td width="25%">
                                        @if(!empty($purchasedClass->webinar))
                                            <p>{{ $purchasedClass->webinar->creator->full_name  }}</p>
                                        @else
                                            <p>{{ $purchasedClass->seller->full_name  }}</p>
                                        @endif
                                    </td>

                                    <td class="text-center">{{ dateTimeFormat($purchasedClass->created_at,'j M Y | H:i') }}</td>

                                    <td class="text-right">
                                        
                                        @can('admin_enrollment_block_access')
                                            @include('admin.includes.delete_button',[
                                                    'url' => getAdminPanelUrl().'/enrollments/'. $purchasedClass->id .'/block-access',
                                                    'tooltip' => trans('update.block_access'),
                                                    'btnIcon' => 'fa-times-circle'
                                                ])
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </table>
                    <p class="font-12 text-gray mt-1 mb-0">{{ trans('update.purchased_hint') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewfile(){
    
    // var viewfile2 = document.getElementById("pre1");
    
    // console.log(viewfile2.src);
    // viewfile2.src = src1;
    $('#textpop1').modal();
    // console.log(viewfile2.src);
  
}
</script>
<style>
@media screen and (max-width: 992px) {
  #pre1 {
      width: -webkit-fill-available;
    height: 283px;
  
  }
  .pdf {
      display:none !important;
  }
}
@media screen and (min-width: 991px) {
  #pre1 {
      width:-webkit-fill-available; 
      height:450px;
  }
  #mob1 {
      
      display:none !important;
  }
  
}
    
</style>