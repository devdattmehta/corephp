<div class="row">
  <div class="panel-heading">
    <div class="row">
      <div class="col-xs-6">
          <h4>Create Split Payment</h4>
      </div>
    </div>
    <hr>
  </div>

  <div class="panel-body">
    <div class="row">
      <div class="col-lg-12">
        <form id="login-form" method="post" action="<?= $_SERVER['PHP_SELF']; ?>" role="form" style="display: block;">
          <div class="form-group">
            <input type="text" name="amounttosplit" id="amounttosplit" tabindex="1" class="form-control" placeholder="Amount" value="<?= empty($vars['balance'])?'':$vars['balance'] ?>" onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,'')" />
            <div class="danger" id="amounttosplitError"></div>
          </div>
          <div class="form-group">
              <input type="text" name="splitAmt" id="splitAmt" tabindex="2" class="form-control" placeholder="Share amount" onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,'')">
              <div class="danger" id="splitAmtError"></div>
          </div>
          <div class="form-group">
            <input type="email" name="emailtonotifiy" id="emailtonotifiy" tabindex="1" class="form-control" placeholder="Email Address" value="">            
            <div class="danger" id="emailtonotifiyError"></div>
          </div>          
          <div class="form-group">
            <div class="row">
              <div class="col-sm-6 col-sm-offset-3">
                <input type="button" name="splitptm-submit" id="splitptm-submit" tabindex="4" class="form-control btn btn-splitptm"
                       value="Submit">
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
    <div class="panel-heading">
      <div class="row">
        <div class="col-xs-6">
            <h4>Notifications Sent</h4>
        </div>
      </div>
      <hr>    
      <div class="row" id="shareAmtemailtonotifiy">
        <?php showNotificationFrom($vars['notifications']) ?>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  $( document ).ready(function() {
    if($("#amounttosplit").val()>0){
      $("#amounttosplit").attr('disabled','disabled');
    }
    $(function () {
        $("#splitptm-submit").click(function () {
            if ($("#amounttosplit").val() == ""){
                $('#amounttosplitError').html("Please input amount!");
                return;
            }
            if ($("#amounttosplit").val() != "" && $("#splitAmt").val() != "") {

                if(parseInt($("#amounttosplit").val()) < parseInt($("#splitAmt").val())){
                  $('#splitAmtError').html("Share amount should not more than Amount!");
                  return;
                }

                if(!validateEmail($("#emailtonotifiy").val())){
                  $('#emailtonotifiyError').html('Invalid Email!');
                  return;
                }

                $.ajax({
                    method: "POST",
                    url: "<?= $_SERVER['PHP_SELF']; ?>",
                    data: {amounttosplit: $("#amounttosplit").val(), 
                           splitAmt: $("#splitAmt").val(), 
                           emailtonotifiy: $("#emailtonotifiy").val(),
                           createNewSplit: 1
                         }
                }).done(function (jsonMsg) {
                    if (jsonMsg !== "") {
                        var obj = JSON.parse(jsonMsg);
                        if(obj.error != ""){
                          if(obj.error.emailtonotifiy != ""){
                            $('#emailtonotifiyError').html(obj.error.emailtonotifiy);
                          }else if(obj.error.splitAmt != ""){
                            $('#splitAmtError').html(obj.error.splitAmt);
                          }
                        }else{
                          var balance = $("#amounttosplit").val()-$("#splitAmt").val();
                          $("#amounttosplit").val(balance);
                          $("#amounttosplit").attr('disabled',true);
                          $('#shareAmtemailtonotifiy').append(jsonMsg.update);
                        }
                    } else {
                        window.location = "<?=userfile?>";
                    }
                });
            } else {
                alert("Please fill all fields with valid data!");
            }
        });
    });
  });
</script>
