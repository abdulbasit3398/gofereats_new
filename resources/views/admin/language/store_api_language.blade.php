@extends('admin/template')
@section('main')
<div class="content" ng-controller="language">
 	<div class="container-fluid">
   	 	<div class="col-md-12">
	      <div class="card ">
		        <div class="card-header card-header-rose card-header-text">
		          <div class="card-text">
		            <h4 class="card-title"> Manage Language file </h4>
		          </div>
		        </div>

		      <div class="" style="border:1px solid #ddd;padding: 15px;margin: 10px;">
			     <strong> Notes:-</strong>
			       <ul style=" list-style: none;">
              <li style="   list-style-type: disc;    list-style-position: inside;">You can change the content by own in the website for all languages. It will get reflects automatically in the front-end. </li>  
            </ul>
			    </div>
			    <div class="row">



      <!-- right column -->
      <div class="col-md-12">
        <!-- Horizontal Form -->
        
          <table id="example" class="table table-striped table-bordered" style="width:100%;border:1px solid #ddd">
        <thead>
            <tr>
                @foreach($all_lanuage as $language)
                  <th>{{$language}}</th>
                @endforeach
            </tr>
        </thead>
         <tbody>
          @foreach ($lang_data['en'] as $main_key => $main_value) 
            @if(is_array($main_value))
              @foreach ($main_value as  $sub_key => $sub_value) 
                <tr>
                  @foreach ($all_lanuage as  $lan_key => $lang_value) 
                    @if(isset($lang_data[$lan_key][$main_key][$sub_key]))
                      <td data-lang="{{$lan_key}}" data-main_key="{{$main_key}}" class="cls_datarelative" data-sub_key="{{$sub_key}}"> {{$lang_data[$lan_key][$main_key][$sub_key]}}</td>
                    @else
                      <td > {{$lang_data['en'][$main_key][$sub_key]}}</td>
                    @endif
                  @endforeach
                </tr>
              @endforeach
            @endif
          @endforeach

        </tbody>
    </table>
      <!-- /.box -->
    </div>
    <!--/.col (right) -->
    </div>
	    	</div>
  		</div>
	</div>
    <div class="modal fade" id="DescModal" data-backdrop="static" data-keyboard="false" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                 <h3 class="modal-title">Job Requirements & Description</h3>
            </div> -->
            <div class="modal-body">
            
            <div class="row dataTable">
                <div class="col-md-4">
                    <label class="control-label" style="color: #333333">Content to be displayed as </label>
                </div>
                <div class="col-md-8">
                    <textarea class="form-control" id="company-full-name" name="companyFullName"></textarea>
                </div>
                </div>

                <br>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default "  data-dismiss="modal" aria-hidden="true">Close</button>
                <button type="button" class="btn btn-primary " data-dismiss="modal" id='update_api_user_language'>Apply!</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
</div>
<style type="text/css">
  #example_wrapper
  {
        overflow-x: auto;
  }
</style>

@endsection