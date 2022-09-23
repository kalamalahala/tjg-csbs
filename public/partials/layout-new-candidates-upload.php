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
                    <!-- Begin hidden, fill and show via AJAX -->
                    <div class="form-group row tjg-csbs-summary">
                        <label for="tjg-csbs-upload-new-candidates-summary" class="col-sm-2 col-form-label">Upload Summary</label>
                        <div class="col-sm-10">
                            <textarea class="form-control" id="tjg-csbs-upload-new-candidates-summary" name="tjg-csbs-upload-new-candidates-summary" rows="8" readonly></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" id="tjg-csbs-upload-submit">Submit Candidate File</button>
                    <button type="button" class="btn btn-secondary" id="tjg-csbs-upload-cancel" >Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>