<?php

/**
 * Form to receive list of DNC candidates to filter against
 */

?>

<div class="form-wrapper w-50">
    <form class="form-horizontal" name="dnc-form" id="dnc-form">
        <fieldset>

            <!-- Form Name -->
            <legend>Do Not Contact Upload</legend>

            <!-- File Button -->
            <div class="form-group row">
                <label class="col-md-4 control-label" for="dnc-file">Upload DNC List</label>
                <div class="col-md-8">
                    <input id="dnc-file" name="dnc-file" class="input-file" type="file" accept=".xls,.xlsx,.csv,.odf">
                    <p id="dnc-file-name"></p>
                </div>
            </div>

            <!-- Textarea -->
            <div class="form-group row">
                <div class="col-md-6">

                    <textarea class="form-control" id="debug-info" name="debug-info" disabled>debug information</textarea>
                </div>
            </div>

            <!-- Button (Double) -->
            <div class="form-group row">
                <div class="col-md-8">
                    <button id="submit-dnc-button" name="submit-dnc-button" class="btn btn-primary" type="submit">Upload File</button>
                    <button id="reset-dnc-button" name="reset-dnc-button" class="btn btn-danger" type="reset">Reset</button>
                </div>
            </div>

        </fieldset>
    </form>

</div>