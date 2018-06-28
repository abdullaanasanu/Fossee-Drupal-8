
// To popup state data
function hello(state,district = false){
  var add = '';
  var sub = '';
  var title = '';
  if (jQuery('#edit-foss-type').val()) {
    sub += jQuery('#edit-foss-type').val();
  }else{
    sub += '0';
  }
  if(jQuery('#edit-foss-activities').val()){
    add += ', "activities" : "'+jQuery('#edit-foss-activities').val()+'"';
    sub += ','+jQuery('#edit-foss-activities').val();
  }else {
    sub += ',0';
  }
  if(jQuery('#edit-foss-status').val()){
    add += ', "status" : "'+jQuery('#edit-foss-status').val()+'"';
    sub += ','+jQuery('#edit-foss-status').val();
  }else {
    sub += ',0';
  }
  if(district){
    title = '<h1>'+district+'</h1><h4>State: '+state+'</h4>';
  }else {
    title = '<h1>'+state+'</h1>';
  }
  // Ajax request to get state data
  jQuery.ajax({
    type: 'POST',
    url: 'state-details',
    data: '{  "type" : "'+jQuery('#edit-foss-type').val()+'", "state" : "'+state+'", "district" : "'+district+'" '+add+'}',
    processData:false,
    contentType: "application/json",
    success:function(r){
      var data = JSON.parse(r);
      var out = '';
      var status = 0;
      var select = '';
      if(data.City.length > 1){
        select += '<div>Place : <select onchange="post_office(\''+state+'\',\''+district+'\','+sub+')" id="posts"><option value="'+district+'">All</option>';
        jQuery(data.City).each(function(i) {
          select += '<option value="'+data.Pincodes[i]+'">'+data.City[i]+'</option>';
        });
        select += '</select></div>';
      }
      if(data.Workshop != 0){
        out += '<h3>Workshop - '+data.Workshop+'</h3>';
        status = 1;
      }
      if(data.Conference != 0){
        out += '<h3>Conference - '+data.Conference+'</h3>';
        status = 1;
      }
      if(data.lab_migration_completed != 0){
        out += '<h3>Lab Migration (Completed) - '+data.lab_migration_completed+'</h3>';
        status = 1;
      }
      if(data.lab_migration_pending != 0){
        out += '<h3>Lab Migration (Pending) - '+data.lab_migration_pending+'</h3>';
        status = 1;
      }
      if(data.PendingBookCount != 0){
        out += '<h3>TextBook Companion (Pending) - '+data.PendingBookCount+'</h3>';
        status = 1;
      }
      if(data.CompletedBookCount != 0){
        out += '<h3>TextBook Companion (Completed) - '+data.CompletedBookCount+'</h3>';
        status = 1;
      }
      if (data.Flowsheet_completed != 0) {
        out += '<h3>DWSIM Flowsheet (Completed)- '+data.Flowsheet_completed+'</h3>';
        status = 1;
      }
      if (data.Flowsheet_pending != 0) {
        out += '<h3>DWSIM Flowsheet (Pending)- '+data.Flowsheet_pending+'</h3>';
        status = 1;
      }
      if (data.circuit_simulation_completed != 0) {
        out += '<h3>eSim Ciruit Simulation (Completed)- '+data.circuit_simulation_completed+'</h3>';
        status = 1;
      }
      if (data.circuit_simulation_pending != 0) {
        out += '<h3>eSim Ciruit Simulation (Pending)- '+data.circuit_simulation_pending+'</h3>';
        status = 1;
      }
      /*if (data.selfworkshop != 0) {
        out += '<h3>Self Workshop - '+data.selfworkshop+'</h3>';
        status = 1;
      }*/
      if (status == 0) {
        out = '<h3>There is no data available</h3>';
      }
      jQuery('body').html(jQuery('body').html() + '<div id="poped"><div id="popup"><div class="head"><span id="closer" onclick="close_popup('+sub+')">&times;</span>'+title+'</div><div class="body">'+select+'<div id="data">'+out+'</div></div></div></div>');
    },error:function(r){
      alert('not working!');
    }
  });
}

// To close popup
function close_popup(type,activities,status) {
  if(type == 0){
    jQuery('#edit-foss-type').val('');
  }else{
    jQuery('#edit-foss-type').val(type);
  }
  jQuery('body #poped').remove();
  if (activities != 0) {
      jQuery('#edit-foss-activities').val(activities);
  }
  if (status != 0) {
      jQuery('#edit-foss-status').val(status);
  }
}

// To generate map according to activities and status
function map(){
  jQuery("#wait").css("display", "block");
  jQuery("#load_map").hide();
  var add = '';
  if(jQuery('#edit-foss-activities').val()){
    add += ', "activities" : "'+jQuery('#edit-foss-activities').val()+'"';
  }
  if(jQuery('#edit-foss-status').val()){
    add += ', "status" : "'+jQuery('#edit-foss-status').val()+'"';
  }
  // Ajax request to generate map
  jQuery.ajax({
    type: 'POST',
    url: 'map-stats',
    data: '{ "type" : "'+jQuery('#edit-foss-type').val()+'"'+add+'}',
    processData:false,
    contentType: "application/json",
    success:function(r){
      jQuery('#load_map').html(r);
      jQuery("#wait").css("display", "none");
      jQuery('#load_map').show();
    },error:function(r){
      alert('not working!');
    }
  });
}

// To generate state map according to activities and status
function state(state){
  jQuery("#wait").css("display", "block");
  jQuery("#load_map").hide();
  var add = '';
  if(jQuery('#edit-foss-activities').val()){
    add += ', "activities" : "'+jQuery('#edit-foss-activities').val()+'"';
  }
  if(jQuery('#edit-foss-status').val()){
    add += ', "status" : "'+jQuery('#edit-foss-status').val()+'"';
  }
  // Ajax request to generate map
  jQuery.ajax({
    async: true,
    type: 'POST',
    url: 'state-map-stats',
    data: '{ "type" : "'+jQuery('#edit-foss-type').val()+'"'+add+', "state" : "'+state+'"}',
    processData:false,
    contentType: "application/json",
    success:function(r){
      jQuery('#load_map').html(r);
      jQuery("#wait").css("display", "none");
      jQuery('#load_map').show();
    },error:function(r){
      alert('not working!');
    }
  });
}

// To enable the activities dropdown
function check_activities() {
  jQuery.ajax({
    type: 'POST',
    url: 'dropdowns',
    data: '{ "Type" : "activities", "ID" : "'+jQuery('#edit-foss-type').val()+'"}',
    processData: false,
    contentType: "application/json",
    success:function(r){
      jQuery('#activities').html(r);
    },error:function(r){
      alert('not working!');
    }
  });
}

// To enable status dropdown
function check_status() {
  jQuery.ajax({
    type: 'POST',
    url: 'dropdowns',
    data: '{ "Type" : "status", "ID" : "'+jQuery('#edit-foss-activities').val()+'"}',
    processData: false,
    contentType: "application/json",
    success:function(r){
      jQuery('#status').html(r);
    },error:function(r) {
      alert('Not working!!!');
    }
  });
}

function post_office(state,district,type,activities,status) {
  var add = '';
  if (activities != 0) {
      add += ', "activities" : "'+activities+'"';
  }
  if (status != 0) {
      add += ', "status" : "'+status+'"';
  }
  jQuery.ajax({
    type: 'POST',
    url: 'post-office-data',
    data: '{ "type" : "'+type+'" '+add+', "pincode" : "'+jQuery('#posts').val()+'", "state" : "'+state+'", "district" : "'+district+'"}',
    processData: false,
    contentType: "application/json",
    success:function(r){
      var data = JSON.parse(r);
      var out ='';
      if(data.Workshop != 0){
        out += '<h3>Workshop - '+data.Workshop+'</h3>';
      }
      if(data.Conference != 0){
        out += '<h3>Conference - '+data.Conference+'</h3>';
      }
      if(data.lab_migration_completed != 0){
        out += '<h3>Lab Migration (Completed) - '+data.lab_migration_completed+'</h3>';
      }
      if(data.lab_migration_pending != 0){
        out += '<h3>Lab Migration (Pending) - '+data.lab_migration_pending+'</h3>';
      }
      if(data.PendingBookCount != 0){
        out += '<h3>TextBook Companion (Pending) - '+data.PendingBookCount+'</h3>';
      }
      if(data.CompletedBookCount != 0){
        out += '<h3>TextBook Companion (Completed) - '+data.CompletedBookCount+'</h3>';
      }
      if (data.Flowsheet_completed != 0) {
        out += '<h3>DWSIM Flowsheet (Completed)- '+data.Flowsheet_completed+'</h3>';
      }
      if (data.Flowsheet_pending != 0) {
        out += '<h3>DWSIM Flowsheet (Pending)- '+data.Flowsheet_pending+'</h3>';
      }
      if (data.circuit_simulation_completed != 0) {
        out += '<h3>eSim Ciruit Simulation (Completed)- '+data.circuit_simulation_completed+'</h3>';
      }
      if (data.circuit_simulation_pending != 0) {
        out += '<h3>eSim Ciruit Simulation (Pending)- '+data.circuit_simulation_pending+'</h3>';
      }
      jQuery('#data').html(out);
    },error:function(r) {
      alert('Not working!!!');
    }
  });
}

  jQuery('#type').change(function (){
    check_activities();
  });
