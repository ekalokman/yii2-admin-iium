<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$searchUrl = Url::to(['user/search']);
$script = <<<JS
$(document).ready(function () {
    let currentPage = 1; // Track the current page

    // Find Staff button click event
    $('#find-staff').on('click', function (event) {
        event.preventDefault(); // Prevent form submission
        currentPage = 1; // Reset to the first page on new search
        searchStaff();
    });

    // Clear button click event
    $('#clear-search').on('click', function (event) {
        event.preventDefault(); // Prevent form submission
        clearSearch();
    });

    // Next page button click event
    $('#next-page').on('click', function (event) {
        event.preventDefault(); // Prevent form submission
        currentPage++;
        searchStaff();
    });

    // Previous page button click event
    $('#prev-page').on('click', function (event) {
        event.preventDefault(); // Prevent form submission
        if (currentPage > 1) {
            currentPage--;
            searchStaff();
        }
    });

    // Enter key event for the search input
    $('#staff-search').on('keydown', function (event) {
        if (event.keyCode === 13) { // 13 is the key code for Enter
            event.preventDefault(); // Prevent form submission
            currentPage = 1; // Reset to the first page on new search
            searchStaff();
        }
    });

    // Function to perform the search
    function searchStaff() {
        var query = $('#staff-search').val();
        // console.log('Searching for:', query);

        if (query) {
            $('#loading-spinner').show(); // Show loading spinner
            $.get('$searchUrl', { query: query, page: currentPage })
                .done(function (response) {
                    $('#loading-spinner').hide(); // Hide loading spinner
                    // console.log('Response:', response);

                    let data;
                    try {
                        data = typeof response === 'string' ? JSON.parse(response) : response;
                    } catch (e) {
                        console.error('Failed to parse JSON:', e);
                        alert('An error occurred while processing the response.');
                        return;
                    }

                    if (data.error) {
                        alert(data.error); // Display server-side error
                        $('#staff-table').hide();
                        $('#pagination-controls').hide();
                        return;
                    }

                    if (data.staff.length === 0) {
                        alert('No staff found.');
                        $('#staff-table').hide();
                        $('#pagination-controls').hide();
                    } else {
                        $('#staff-table').show();
                        $('#pagination-controls').show();

                        // Update the table with the new data
                        var tableBody = $('#staff-table tbody');
                        tableBody.empty();

                        // Calculate the starting number for the current page
                        var startNumber = (currentPage - 1) * 10 + 1;

                        data.staff.forEach(function(staff, index) {
                            var rowNumber = startNumber + index; // Calculate row number
                            var username = staff.sm_email_addr.replace('@iium.edu.my', '');

                            var row = '<tr>' +
                                '<td>' + rowNumber + '</td>' + // Add row number
                                '<td>' + staff.sm_staff_id + '</td>' +
                                '<td>' + staff.sm_staff_name + '</td>' +
                                '<td>' + staff.sm_email_addr + '</td>' +
                                '<td>' +
                                '<a href="add-user?staff_no=' + staff.sm_staff_id + '" class="btn btn-primary add-user-btn" data-staff-no="' + staff.sm_staff_id + '">Add User</a>' +
                                '</td>' +
                                '</tr>';
                            tableBody.append(row);
                        });

                        // Update pagination controls
                        $('#page-info').text('Page ' + data.pagination.currentPage + ' of ' + data.pagination.pageCount);
                        $('#total-count').text('Total Results: ' + data.pagination.totalCount);

                        // Enable/disable pagination buttons
                        $('#prev-page').prop('disabled', currentPage === 1);
                        $('#next-page').prop('disabled', currentPage === data.pagination.pageCount);

                        // Reset the Next button text if not on the last page
                        if (currentPage < data.pagination.pageCount) {
                            $('#next-page').text('Next');
                        } else {
                            $('#next-page').text('No More Results');
                        }
                    }
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    $('#loading-spinner').hide(); // Hide loading spinner on error
                    console.error('AJAX Error:', textStatus, errorThrown);
                    alert('An error occurred while fetching data. Please try again.');
                });
        } else {
            alert('Please enter a search query.');
        }
    }

    // Function to clear the search
    function clearSearch() {
        $('#staff-search').val(''); // Clear the search input
        $('#staff-table').hide(); // Hide the table
        $('#pagination-controls').hide(); // Hide pagination controls
        currentPage = 1; // Reset the current page to 1
        $('#page-info').text(''); // Clear page info
        $('#total-count').text(''); // Clear total count
        $('#next-page').text('Next'); // Reset the Next button text
    }

    $(document).on('click', '.add-user-btn', function (event) {
        event.preventDefault();
        let staffNo = $(this).data('staff-no'); // Get the staff_no from the button

        $.ajax({
            url: 'check-user', // Endpoint to check staff existence
            type: 'GET',
            data: { staff_no: staffNo },
            success: function (response) {
                if (response.exists) {
                    alert('The user is already added.');
                } else {
                    window.location.href = 'add-user?staff_no=' + staffNo;
                }
            },
            error: function () {
                alert('An error occurred while checking user existence.');
            }
        });
    });

});
JS;

$this->registerJs($script);
?>

<style>
    /* Custom CSS for the form and table */
    .user-form {
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
        transition: background-color 0.3s ease;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
    }

    #staff-table {
        width: 100%;
        margin-top: 20px;
    }

    #staff-table td, #staff-table th {
        padding: 12px;
        text-align: left;
    }

    #staff-table tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    #staff-table tr:hover {
        background-color: #ddd;
    }

    #pagination-controls {
        margin-top: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #loading-spinner {
        text-align: center;
        margin-top: 20px;
    }

    .fa-spinner {
        font-size: 24px;
        color: #007bff;
    }
</style>

<div id="loading-spinner" style="display: none;">
    <i class="fa fa-spinner fa-spin"></i> Loading...
</div>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-xl-12">
            <!-- Search Input -->
            <div class="mb-3 row align-items-center">
                <label for="staff-search" class="col-lg-2 col-form-label">Search by Staff No or Name<span class="text-danger"> *</span></label>
                <div class="col-lg-6">
                    <?= $form->field($model, 'username')->textInput([
                        'placeholder' => 'Enter Staff No or Name',
                        'id' => 'staff-search',
                        'class' => 'form-control'
                    ])->label(false) ?>
                </div>
                <div class="col-lg-4 d-flex gap-2">
                    <button type="button" id="find-staff" class="btn btn-primary flex-fill">
                        <i class="fa fa-search"></i> Find Staff
                    </button>
                    <button type="button" id="clear-search" class="btn btn-danger flex-fill">
                        <i class="fa fa-times"></i> Clear
                    </button>
                </div>
            </div>

            <!-- Staff Table -->
            <div id="staff-table" style="display: none;">
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-responsive-sm">
                            <thead>
                                <tr>
                                    <th>#</th> <!-- No. column -->
                                    <th>Staff No</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Rows will be populated here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination Controls -->
            <div id="pagination-controls" style="display: none; margin-top: 20px;">
                <button type="button" id="prev-page" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Previous
                </button>
                <span id="page-info" style="margin: 0 10px;"></span>
                <button type="button" id="next-page" class="btn btn-secondary">
                    Next <i class="fa fa-arrow-right"></i>
                </button>
                <span id="total-count" style="margin-left: 20px;"></span>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
