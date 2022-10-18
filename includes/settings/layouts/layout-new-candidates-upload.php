<?php

/**
 * This is the upload form for the raw candidate data.
 * 
 * @since 1.0.0
 * 
 * Use bootstrap 4 classes to create the form.
 */

?>

<div class="tjg-csbs-form-wrapper">
    <div class="container-fluid">
        <h3 class="mt-0">Upload Candidate Data</h3>
        <div class="row">
            <div class="col">
                <form id="tjg-csbs-upload-new-candidates" method="post" enctype="multipart/form-data" action="">
                    <div class="form-group row">
                        <label for="tjg-csbs-upload-new-candidates-file" class="col-sm-2 col-form-label">Upload File</label>
                        <div class="col-sm-10">
                            <input type="file" class="form-control-file" id="tjg-csbs-upload-new-candidates-file" name="tjg-csbs-upload-new-candidates-file" accept=".xls,.xlsx">
                            <!-- Trash icon to clear file input -->
                            <div class="tjg-csbs-trash-file" id="tjg-csbs-upload-new-candidates-trash" hidden>
                                <i class="fa fa-trash-alt tjg-csbs-clear-file-input"></i>
                                <p>Clear File</p>
                            </div>
                        </div>
                    </div>
                    <!-- Upload Contents Summary -->
                    <div class="form-group row tjg-csbs-summary">
                        <label for="tjg-csbs-upload-new-candidates-summary" class="col-sm-2 col-form-label">Upload Summary</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" id="tjg-csbs-upload-new-candidates-summary" name="tjg-csbs-upload-new-candidates-summary" rows="8" readonly></textarea>
                        </div>
                    </div>
                    <!-- End Upload Contents Summary -->
                    <!-- Select and Option groups for First Name, Last Name, Email, Phone Number, City, and State -->
                    <div id="select-group-wrapper" class="column-select-group-wrapper" hidden>
                        <div class="row column-select-group">
                            <h3>Select Columns in Sheet</h3>
                        </div>
                        <div class="form-group row column-select-group">
                        <label for="tjg-csbs-upload-new-candidates-first-name" class="col-sm-2 col-form-label">First Name</label>
                        <div class="col-sm-7">
                            <select class="form-control" id="tjg-csbs-upload-new-candidates-first-name" name="tjg-csbs-upload-new-candidates-first-name">
                                <option value="0">Select Column</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row column-select-group">
                        <label for="tjg-csbs-upload-new-candidates-last-name" class="col-sm-2 col-form-label">Last Name</label>
                        <div class="col-sm-7">
                            <select class="form-control" id="tjg-csbs-upload-new-candidates-last-name" name="tjg-csbs-upload-new-candidates-last-name">
                                <option value="0">Select Column</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row column-select-group">
                        <label for="tjg-csbs-upload-new-candidates-email" class="col-sm-2 col-form-label">Email</label>
                        <div class="col-sm-7">
                            <select class="form-control" id="tjg-csbs-upload-new-candidates-email" name="tjg-csbs-upload-new-candidates-email">
                                <option value="0">Select Column</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row column-select-group">
                        <label for="tjg-csbs-upload-new-candidates-phone" class="col-sm-2 col-form-label">Phone Number</label>
                        <div class="col-sm-7">
                            <select class="form-control" id="tjg-csbs-upload-new-candidates-phone" name="tjg-csbs-upload-new-candidates-phone">
                                <option value="0">Select Column</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row column-select-group">
                        <label for="tjg-csbs-upload-new-candidates-city" class="col-sm-2 col-form-label">City</label>
                        <div class="col-sm-7">
                            <select class="form-control" id="tjg-csbs-upload-new-candidates-city" name="tjg-csbs-upload-new-candidates-city">
                                <option value="0">Select Column</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row column-select-group">
                        <label for="tjg-csbs-upload-new-candidates-state" class="col-sm-2 col-form-label">State</label>
                        <div class="col-sm-7">
                            <select class="form-control" id="tjg-csbs-upload-new-candidates-state" name="tjg-csbs-upload-new-candidates-state">
                                <option value="0">Select Column</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row column-select-group">
                        <label for="tjg-csbs-upload-new-candidates-source" class="col-sm-2 col-form-label">Recruit Source</label>
                        <div class="col-sm-7">
                            <select class="form-control" id="tjg-csbs-upload-new-candidates-source" name="tjg-csbs-upload-new-candidates-source">
                                <option value="0">Select Column</option>
                            </select>
                        </div>
                    </div>
                </div>
                    <button type="submit" class="btn btn-primary" id="tjg-csbs-upload-submit">Submit Candidate File</button>
                    <button type="button" class="btn btn-secondary" id="tjg-csbs-upload-cancel" >Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>