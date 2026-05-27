<div class="row grid-margin">
    <div class="col-lg-12">
        <div class="card">
        <div class="card-body">
            <h4 class="card-title"> Add Ticket </h4>
            <form class="cmxform" id="commentForm" method="get" action="#">
            <fieldset>
                <div class="form-group">
                    <label for="cname">Contact No</label>
                    <input id="cname" class="form-control" name="name" minlength="2" type="text" required placeholder="Mobile number">
                </div>
                <div class="form-group">
                    <label for="cname">Subject</label>
                    <input id="cname" class="form-control" name="name" minlength="2" type="text" required>
                </div>
                <div class="form-group">
                    <label for="exampleSelectGender">Gender</label>
                    <select class="form-select" id="exampleSelectGender">
                    <option>Male</option>
                    <option>Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="ccomment">Description (required)</label>
                    <textarea id="ccomment" class="form-control" name="comment" required></textarea>
                </div>
                <input class="btn btn-primary" type="submit" value="Submit">
            </fieldset>
            </form>
        </div>
        </div>
    </div>
</div>