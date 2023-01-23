<div class="form-group">
    <input type="text" name="fname" id="fname" tabindex="1" class="form-control" placeholder="First name" value="">
</div>
<div class="form-group">
    <input type="text" name="lname" id="lname" tabindex="1" class="form-control" placeholder="Last name" value="">
</div>
<div class="form-group">
    <input type="email" name="email" id="email" tabindex="1" class="form-control" placeholder="Email Address" value="">
</div>
<div class="form-group">
    <input type="file" name="image" id="image" tabindex="1" class="form-control" >
</div>
<div class="form-group">
    <input type="password" name="password" id="password2" tabindex="2" class="form-control" placeholder="Password">
</div>
<div class="form-group">
    <input type="password" name="confirm-password" id="confirm-password" tabindex="2" class="form-control"
           placeholder="Confirm Password">
</div>
<div class="form-group">
    <div class="row">
        <div class="col-sm-6 col-sm-offset-3">
            <input type="button" name="register-submit" id="register-submit" tabindex="4"
                   class="form-control btn btn-register" value="Register Now">
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function () {
        $("#register-submit").click(function () {
            if ($("#fname").val() != "" && $("#lname").val() != "" && $("#email").val() != "" && $("#password2").val() != "" && validateEmail($("#email").val())) {
                if ($("#password2").val() === $("#confirm-password").val()) {
                    var form = $('#register-form')[0];
                    var formData = new FormData(form);
                    $.ajax({
                        method: "POST",
                        url: "<?=registerfile?>",
                        data: formData,
                        contentType: false,
                        processData: false,
                    }).done(function (msg) {

                        var responseData = $.parseJSON(msg);
                        alert(responseData.message);
                        if(responseData.status)
                        window.location = "<?=userfile?>";
                    });

                } else {
                    alert("Passwords do not match!");
                }

            } else {
                alert("Please fill all fields with valid data!");
            }
        });
    });
</script>
