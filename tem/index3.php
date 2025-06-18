<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Explorer</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        .hierarchy-node {
            transition: all 0.3s ease;
        }
        .hierarchy-node:hover {
            transform: translateX(5px);
            background-color: #f8f9fa;
        }
        .filter-section {
            background-color: #f0f8ff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .accordion-button:not(.collapsed) {
            background-color: #e3f2fd;
        }
        .tree-arrow {
            transition: transform 0.3s ease;
        }
        .tree-arrow.rotated {
            transform: rotate(90deg);
        }
        .badge-light-blue {
            background-color: #e3f2fd;
            color: #0d6efd;
        }
        .badge-light-green {
            background-color: #d1e7dd;
            color: #0a3622;
        }
        .table-container {
            max-height: 500px;
            overflow-y: auto;
        }
    </style>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0d6efd',
                        secondary: '#6c757d',
                        accent: '#198754',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <div class="container py-5">
        <!-- Header -->
        <header class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-primary mb-3">University Explorer</h1>
            <p class="text-gray-600 max-w-3xl mx-auto">Discover universities, courses, and programs across India. Filter by multiple criteria and explore course hierarchies.</p>
        </header>
        
        <!-- Filters Section -->
        <section class="filter-section p-4 mb-6">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="text-xl font-semibold text-gray-700">Filter Options</h2>
                <button id="resetFilters" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i> Reset Filters
                </button>
            </div>
            
            <div class="accordion" id="filterAccordion">
                <!-- Location Filters -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#locationFilters">
                            <i class="bi bi-geo-alt me-2"></i> Location
                        </button>
                    </h2>
                    <div id="locationFilters" class="accordion-collapse collapse show" data-bs-parent="#filterAccordion">
                        <div class="accordion-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium">States</label>
                                    <select class="form-select filter-select" multiple id="stateFilter">
                                        <option value="1">Maharashtra</option>
                                        <option value="2">Karnataka</option>
                                        <option value="3">Tamil Nadu</option>
                                        <option value="4">Delhi</option>
                                        <option value="5">Uttar Pradesh</option>
                                        <option value="6">West Bengal</option>
                                        <option value="7">Gujarat</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium">Cities</label>
                                    <select class="form-select filter-select" multiple id="cityFilter">
                                        <option value="1">Mumbai</option>
                                        <option value="2">Pune</option>
                                        <option value="3">Bangalore</option>
                                        <option value="4">Chennai</option>
                                        <option value="5">New Delhi</option>
                                        <option value="6">Kolkata</option>
                                        <option value="7">Ahmedabad</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- University Filters -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#universityFilters">
                            <i class="bi bi-building me-2"></i> University Details
                        </button>
                    </h2>
                    <div id="universityFilters" class="accordion-collapse collapse" data-bs-parent="#filterAccordion">
                        <div class="accordion-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-medium">Affiliations</label>
                                    <select class="form-select filter-select" multiple id="affiliationFilter">
                                        <option value="1">UGC</option>
                                        <option value="2">AICTE</option>
                                        <option value="3">MCI</option>
                                        <option value="4">NCTE</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-medium">Category</label>
                                    <select class="form-select filter-select" multiple id="categoryFilter">
                                        <option value="IIT">IIT</option>
                                        <option value="NIT">NIT</option>
                                        <option value="AIIMS">AIIMS</option>
                                        <option value="IIIT">IIIT</option>
                                        <option value="IIM">IIM</option>
                                        <option value="NLU">NLU</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-medium">Type</label>
                                    <select class="form-select filter-select" multiple id="typeFilter">
                                        <option value="private">Private</option>
                                        <option value="government">Government</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Course Filters -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#courseFilters">
                            <i class="bi bi-journal-bookmark me-2"></i> Course Details
                        </button>
                    </h2>
                    <div id="courseFilters" class="accordion-collapse collapse" data-bs-parent="#filterAccordion">
                        <div class="accordion-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-medium">Streams</label>
                                    <select class="form-select filter-select" multiple id="streamFilter">
                                        <option value="1">Engineering</option>
                                        <option value="2">Medical</option>
                                        <option value="3">Management</option>
                                        <option value="4">Law</option>
                                        <option value="5">Arts</option>
                                        <option value="6">Science</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-medium">Courses</label>
                                    <select class="form-select filter-select" multiple id="courseFilter">
                                        <option value="1">B.Tech</option>
                                        <option value="2">MBBS</option>
                                        <option value="3">MBA</option>
                                        <option value="4">LLB</option>
                                        <option value="5">B.Sc</option>
                                        <option value="6">B.A.</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-medium">Sub-Streams</label>
                                    <select class="form-select filter-select" multiple id="subStreamFilter">
                                        <option value="1">Computer Science</option>
                                        <option value="2">Mechanical</option>
                                        <option value="3">Electrical</option>
                                        <option value="4">Civil</option>
                                        <option value="5">Cardiology</option>
                                        <option value="6">Neurology</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Filters -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#additionalFilters">
                            <i class="bi bi-funnel me-2"></i> Additional Filters
                        </button>
                    </h2>
                    <div id="additionalFilters" class="accordion-collapse collapse" data-bs-parent="#filterAccordion">
                        <div class="accordion-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-medium">Program Type</label>
                                    <select class="form-select filter-select" multiple id="programTypeFilter">
                                        <option value="1">Full Time</option>
                                        <option value="2">Part Time</option>
                                        <option value="3">Online</option>
                                        <option value="4">Distance</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-medium">Entrance Exams</label>
                                    <select class="form-select filter-select" multiple id="examFilter">
                                        <option value="1">JEE Main</option>
                                        <option value="2">NEET</option>
                                        <option value="3">CAT</option>
                                        <option value="4">CLAT</option>
                                        <option value="5">GATE</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-medium">Duration</label>
                                    <select class="form-select filter-select" multiple id="durationFilter">
                                        <option value="1">1 Year</option>
                                        <option value="2">2 Years</option>
                                        <option value="3">3 Years</option>
                                        <option value="4">4 Years</option>
                                        <option value="5">5 Years</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Avg. Fee per Year (₹)</label>
                                    <div class="d-flex align-items-center">
                                        <input type="number" id="minFee" class="form-control me-2" placeholder="Min" min="0">
                                        <span class="me-2">to</span>
                                        <input type="number" id="maxFee" class="form-control" placeholder="Max" min="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">University Rank</label>
                                    <div class="d-flex align-items-center">
                                        <input type="number" id="minRank" class="form-control me-2" placeholder="Min" min="1">
                                        <span class="me-2">to</span>
                                        <input type="number" id="maxRank" class="form-control" placeholder="Max" min="1">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end mt-3">
                <button id="applyFilters" class="btn btn-primary px-4">
                    <i class="bi bi-filter me-2"></i> Apply Filters
                </button>
            </div>
        </section>
        
        <!-- Results Table -->
        <section class="mb-6">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="text-xl font-semibold text-gray-700">University Programs</h2>
                <div class="d-flex">
                    <div class="input-group me-2" style="width: 250px;">
                        <input type="text" id="searchTable" class="form-control" placeholder="Search...">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="table-container border rounded">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>University</th>
                            <th>Location</th>
                            <th>Category</th>
                            <th>Stream</th>
                            <th>Course</th>
                            <th>Duration</th>
                            <th>Avg. Fee (₹)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="universityTable">
                        <!-- Data will be populated by JavaScript -->
                        <tr>
                            <td>IIT Bombay</td>
                            <td>Mumbai, Maharashtra</td>
                            <td><span class="badge bg-primary">IIT</span></td>
                            <td>Engineering</td>
                            <td>B.Tech</td>
                            <td>4 Years</td>
                            <td>2,50,000</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary view-hierarchy" data-id="1">
                                    <i class="bi bi-diagram-3"></i> View
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>AIIMS Delhi</td>
                            <td>New Delhi, Delhi</td>
                            <td><span class="badge bg-danger">AIIMS</span></td>
                            <td>Medical</td>
                            <td>MBBS</td>
                            <td>5.5 Years</td>
                            <td>10,000</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary view-hierarchy" data-id="2">
                                    <i class="bi bi-diagram-3"></i> View
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>IIM Ahmedabad</td>
                            <td>Ahmedabad, Gujarat</td>
                            <td><span class="badge bg-success">IIM</span></td>
                            <td>Management</td>
                            <td>MBA</td>
                            <td>2 Years</td>
                            <td>11,00,000</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary view-hierarchy" data-id="3">
                                    <i class="bi bi-diagram-3"></i> View
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>NLSIU Bangalore</td>
                            <td>Bangalore, Karnataka</td>
                            <td><span class="badge bg-warning text-dark">NLU</span></td>
                            <td>Law</td>
                            <td>LLB</td>
                            <td>5 Years</td>
                            <td>2,80,000</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary view-hierarchy" data-id="4">
                                    <i class="bi bi-diagram-3"></i> View
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>NIT Trichy</td>
                            <td>Tiruchirappalli, Tamil Nadu</td>
                            <td><span class="badge bg-info">NIT</span></td>
                            <td>Engineering</td>
                            <td>M.Tech</td>
                            <td>2 Years</td>
                            <td>1,20,000</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary view-hierarchy" data-id="5">
                                    <i class="bi bi-diagram-3"></i> View
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>Jadavpur University</td>
                            <td>Kolkata, West Bengal</td>
                            <td><span class="badge bg-secondary">State University</span></td>
                            <td>Arts</td>
                            <td>B.A. English</td>
                            <td>3 Years</td>
                            <td>15,000</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary view-hierarchy" data-id="6">
                                    <i class="bi bi-diagram-3"></i> View
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
        
        <!-- Hierarchy Section -->
        <section class="bg-white p-4 rounded shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-700">Course Hierarchy</h2>
                <button id="clearHierarchy" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Clear View
                </button>
            </div>
            
            <div id="hierarchyContainer" class="p-3 border rounded bg-light">
                <div class="text-center text-muted py-5" id="hierarchyPlaceholder">
                    <i class="bi bi-diagram-3 fs-1 mb-3"></i>
                    <p class="mb-0">Select a course from the table above to view its hierarchy</p>
                </div>
                
                <div id="hierarchyView" class="d-none">
                    <div class="hierarchy-node mb-3 p-3 border-start border-3 border-primary bg-white rounded shadow-sm">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-bookmarks-fill text-primary fs-4 me-3"></i>
                            <div>
                                <h4 class="mb-1" id="hierarchyStream">Engineering</h4>
                                <p class="mb-0 text-muted">Stream</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="hierarchy-node mb-3 ms-4 p-3 border-start border-3 border-success bg-white rounded shadow-sm">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-journal-bookmark-fill text-success fs-4 me-3"></i>
                            <div>
                                <h4 class="mb-1" id="hierarchyCourse">B.Tech</h4>
                                <p class="mb-0 text-muted">Course</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="ms-8">
                        <h5 class="mb-3 fw-medium">Sub-Streams:</h5>
                        <div id="subStreamsContainer" class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title d-flex align-items-center">
                                            <i class="bi bi-code-slash text-info me-2"></i> Computer Science
                                        </h6>
                                        <div class="d-flex small">
                                            <span class="badge badge-light-blue me-2">Core</span>
                                            <span class="badge badge-light-green">4 Years</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title d-flex align-items-center">
                                            <i class="bi bi-gear text-info me-2"></i> Mechanical Engineering
                                        </h6>
                                        <div class="d-flex small">
                                            <span class="badge badge-light-blue me-2">Core</span>
                                            <span class="badge badge-light-green">4 Years</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title d-flex align-items-center">
                                            <i class="bi bi-lightning-charge text-info me-2"></i> Electrical Engineering
                                        </h6>
                                        <div class="d-flex small">
                                            <span class="badge badge-light-blue me-2">Core</span>
                                            <span class="badge badge-light-green">4 Years</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title d-flex align-items-center">
                                            <i class="bi bi-building text-info me-2"></i> Civil Engineering
                                        </h6>
                                        <div class="d-flex small">
                                            <span class="badge badge-light-blue me-2">Core</span>
                                            <span class="badge badge-light-green">4 Years</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Handle filter select styling
            $('.filter-select').on('change', function() {
                if ($(this).val() && $(this).val().length > 0) {
                    $(this).addClass('border-primary');
                } else {
                    $(this).removeClass('border-primary');
                }
            });
            
            // Reset filters
            $('#resetFilters').click(function() {
                $('.filter-select').val(null).trigger('change').removeClass('border-primary');
                $('#minFee, #maxFee, #minRank, #maxRank').val('');
            });
            
            // Apply filters
            $('#applyFilters').click(function() {
                // In a real app, this would make an AJAX request to the server
                // For this demo, we'll just show a notification
                const selectedCount = $('.filter-select').filter(function() {
                    return $(this).val() && $(this).val().length > 0;
                }).length;
                
                const additionalFilters = ($('#minFee').val() || $('#maxFee').val() || 
                                          $('#minRank').val() || $('#maxRank').val());
                
                if (selectedCount > 0 || additionalFilters) {
                    const toast = `<div class="toast align-items-center text-white bg-primary border-0 position-fixed bottom-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                Filters applied successfully
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>`;
                    
                    $('body').append(toast);
                    $('.toast').toast('show');
                    
                    setTimeout(() => {
                        $('.toast').toast('hide');
                    }, 3000);
                }
            });
            
            // Search table
            $('#searchTable').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                $('#universityTable tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
            
            // View hierarchy
            $('.view-hierarchy').click(function() {
                const id = $(this).data('id');
                const row = $(this).closest('tr');
                
                // Get data from the row
                const stream = row.find('td:eq(3)').text();
                const course = row.find('td:eq(4)').text();
                const university = row.find('td:eq(0)').text();
                
                // Update hierarchy view
                $('#hierarchyStream').text(stream);
                $('#hierarchyCourse').text(course + ' - ' + university);
                
                // Show hierarchy and hide placeholder
                $('#hierarchyPlaceholder').addClass('d-none');
                $('#hierarchyView').removeClass('d-none');
                
                // Scroll to hierarchy section
                $('html, body').animate({
                    scrollTop: $('#hierarchyContainer').offset().top - 20
                }, 500);
            });
            
            // Clear hierarchy view
            $('#clearHierarchy').click(function() {
                $('#hierarchyPlaceholder').removeClass('d-none');
                $('#hierarchyView').addClass('d-none');
            });
            
            // Demo data for sub-streams
            const subStreamsData = {
                1: [
                    {name: "Computer Science", icon: "bi-code-slash", type: "Core"},
                    {name: "Mechanical Engineering", icon: "bi-gear", type: "Core"},
                    {name: "Electrical Engineering", icon: "bi-lightning-charge", type: "Core"},
                    {name: "Civil Engineering", icon: "bi-building", type: "Core"}
                ],
                2: [
                    {name: "General Medicine", icon: "bi-heart-pulse", type: "Core"},
                    {name: "Surgery", icon: "bi-bandaid", type: "Core"},
                    {name: "Pediatrics", icon: "bi-emoji-smile", type: "Specialization"}
                ],
                3: [
                    {name: "Finance", icon: "bi-cash-coin", type: "Specialization"},
                    {name: "Marketing", icon: "bi-graph-up", type: "Specialization"},
                    {name: "Operations", icon: "bi-diagram-3", type: "Specialization"},
                    {name: "Human Resources", icon: "bi-people", type: "Specialization"}
                ],
                4: [
                    {name: "Corporate Law", icon: "bi-briefcase", type: "Specialization"},
                    {name: "Criminal Law", icon: "bi-shield-shaded", type: "Specialization"},
                    {name: "Constitutional Law", icon: "bi-journal-text", type: "Core"}
                ],
                5: [
                    {name: "Computer Science", icon: "bi-code-slash", type: "Core"},
                    {name: "Data Science", icon: "bi-bar-chart", type: "Specialization"},
                    {name: "VLSI Design", icon: "bi-cpu", type: "Specialization"}
                ],
                6: [
                    {name: "English Literature", icon: "bi-journal-text", type: "Core"},
                    {name: "History", icon: "bi-clock-history", type: "Elective"},
                    {name: "Political Science", icon: "bi-building", type: "Elective"}
                ]
            };
            
            // Update sub-streams based on selection
            $('.view-hierarchy').click(function() {
                const id = $(this).data('id');
                const subStreams = subStreamsData[id] || [];
                
                let html = '';
                subStreams.forEach(sub => {
                    html += `<div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title d-flex align-items-center">
                                    <i class="bi ${sub.icon} text-info me-2"></i> ${sub.name}
                                </h6>
                                <div class="d-flex small">
                                    <span class="badge badge-light-blue me-2">${sub.type}</span>
                                    <span class="badge badge-light-green">${id === 2 ? '5.5 Years' : id === 4 ? '5 Years' : id === 6 ? '3 Years' : '4 Years'}</span>
                                </div>
                            </div>
                        </div>
                    </div>`;
                });
                
                $('#subStreamsContainer').html(html);
            });
        });
    </script>
</body>
</html>