<?php
// Database configuration
$host = 'localhost';
$dbname = 'digiperform';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Directory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .logo-cell { width: 80px; }
        .university-table { overflow-x: auto; }
        .filter-container { overflow-x: auto; white-space: nowrap; }
        .filter-card { display: inline-block; width: 250px; margin-right: 15px; }
        .sortable { cursor: pointer; position: relative; }
        .sortable::after { content: "↕"; position: absolute; right: 5px; }
        .sort-asc::after { content: "↑"; }
        .sort-desc::after { content: "↓"; }
        .course-badge { cursor: pointer; }
        .course-details { display: none; background: #f8f9fa; }
        .course-details.active { display: table-row; }
        .accordion-toggle { cursor: pointer; }
    </style>
</head>
<body>
    <div class="container py-4">
        <h1 class="mb-4 text-center">University Directory</h1>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-filter me-2"></i>Filter Universities</span>
                <button id="resetFilters" class="btn btn-sm btn-light">
                    <i class="fas fa-undo me-1"></i> Reset Filters
                </button>
            </div>
            <div class="card-body filter-container p-3">
                <!-- Stream Filter -->
                <div class="filter-card card">
                    <div class="card-header py-2">By Stream</div>
                    <div class="card-body">
                        <?php
                        $streams = $pdo->query("SELECT * FROM streams")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($streams as $stream): ?>
                            <div class="form-check">
                                <input class="form-check-input stream-filter" type="checkbox" 
                                    value="<?= $stream['id'] ?>" id="stream<?= $stream['id'] ?>">
                                <label class="form-check-label" for="stream<?= $stream['id'] ?>">
                                    <?= htmlspecialchars($stream['name']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Course Filter -->
                <div class="filter-card card">
                    <div class="card-header py-2">By Course</div>
                    <div class="card-body" id="courseFilterContainer">
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- State Filter -->
                <div class="filter-card card">
                    <div class="card-header py-2">By State</div>
                    <div class="card-body">
                        <?php
                        $states = $pdo->query("SELECT * FROM states")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($states as $state): ?>
                            <div class="form-check">
                                <input class="form-check-input state-filter" type="checkbox" 
                                    value="<?= $state['id'] ?>" id="state<?= $state['id'] ?>">
                                <label class="form-check-label" for="state<?= $state['id'] ?>">
                                    <?= htmlspecialchars($state['name']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- City Filter -->
                <div class="filter-card card">
                    <div class="card-header py-2">By City</div>
                    <div class="card-body" id="cityFilterContainer">
                        <div class="text-center py-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Affiliation Filter -->
                <div class="filter-card card">
                    <div class="card-header py-2">By Affiliation</div>
                    <div class="card-body">
                        <?php
                        $affiliations = $pdo->query("SELECT * FROM affiliations")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($affiliations as $aff): ?>
                            <div class="form-check">
                                <input class="form-check-input affiliation-filter" type="checkbox" 
                                    value="<?= $aff['id'] ?>" id="aff<?= $aff['id'] ?>">
                                <label class="form-check-label" for="aff<?= $aff['id'] ?>">
                                    <?= htmlspecialchars($aff['name']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Category Filter -->
                <div class="filter-card card">
                    <div class="card-header py-2">By Category</div>
                    <div class="card-body">
                        <?php
                        $categories = ['IIT', 'NIT', 'AIIMS', 'IIIT', 'IIM', 'NLU', 'NIFT', 'IISER', 'FDDI', 'NIPER'];
                        foreach ($categories as $category): ?>
                            <div class="form-check">
                                <input class="form-check-input category-filter" type="checkbox" 
                                    value="<?= $category ?>" id="cat<?= $category ?>">
                                <label class="form-check-label" for="cat<?= $category ?>">
                                    <?= $category ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Universities Table -->
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-university me-2"></i>Universities</span>
                <span id="resultCount" class="badge bg-light text-dark">Loading...</span>
            </div>
            <div class="university-table">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="sortable" data-sort="rank">Rank</th>
                            <th>Logo</th>
                            <th class="sortable" data-sort="name">University Name</th>
                            <th class="sortable" data-sort="affiliation">Affiliation</th>
                            <th class="sortable" data-sort="category">Category</th>
                            <th class="sortable" data-sort="state">State</th>
                            <th class="sortable" data-sort="city">City</th>
                            <th>Streams</th>
                            <th>Courses</th>
                        </tr>
                    </thead>
                    <tbody id="universityData">
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const universityTable = document.getElementById('universityData');
        const courseFilterContainer = document.getElementById('courseFilterContainer');
        const cityFilterContainer = document.getElementById('cityFilterContainer');
        const resultCount = document.getElementById('resultCount');
        let currentSort = 'rank';
        let sortDirection = 'ASC';
        let activeFilters = {
            streams: [],
            courses: [],
            states: [],
            cities: [],
            affiliations: [],
            categories: []
        };

        // Initialize sorting indicators
        document.querySelectorAll('[data-sort]').forEach(header => {
            header.addEventListener('click', function() {
                const sortField = this.dataset.sort;
                
                // Update sort direction
                if (currentSort === sortField) {
                    sortDirection = sortDirection === 'ASC' ? 'DESC' : 'ASC';
                } else {
                    currentSort = sortField;
                    sortDirection = 'ASC';
                }
                
                // Update UI
                document.querySelectorAll('[data-sort]').forEach(h => {
                    h.classList.remove('sort-asc', 'sort-desc');
                });
                this.classList.add(sortDirection === 'ASC' ? 'sort-asc' : 'sort-desc');
                
                loadUniversities();
            });
        });

        // Load courses based on selected streams
        function loadCourses() {
            if (activeFilters.streams.length === 0) {
                courseFilterContainer.innerHTML = '<div class="text-muted">Select streams to see courses</div>';
                return;
            }

            fetch('get_courses.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({streamIds: activeFilters.streams})
            })
            .then(response => response.json())
            .then(courses => {
                let html = '';
                if (courses.length === 0) {
                    html = '<div class="text-muted">No courses found</div>';
                } else {
                    courses.forEach(course => {
                        html += `
                        <div class="form-check">
                            <input class="form-check-input course-filter" type="checkbox" 
                                value="${course.id}" id="course${course.id}"
                                ${activeFilters.courses.includes(course.id) ? 'checked' : ''}>
                            <label class="form-check-label" for="course${course.id}">
                                ${course.name}
                            </label>
                        </div>`;
                    });
                }
                courseFilterContainer.innerHTML = html;
                
                // Add event listeners to new course checkboxes
                document.querySelectorAll('.course-filter').forEach(checkbox => {
                    checkbox.addEventListener('change', handleCourseFilter);
                });
            });
        }

        // Load cities based on selected states
        function loadCities() {
            if (activeFilters.states.length === 0) {
                cityFilterContainer.innerHTML = '<div class="text-muted">Select states to see cities</div>';
                return;
            }

            fetch('get_cities.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({stateIds: activeFilters.states})
            })
            .then(response => response.json())
            .then(cities => {
                let html = '';
                if (cities.length === 0) {
                    html = '<div class="text-muted">No cities found</div>';
                } else {
                    cities.forEach(city => {
                        html += `
                        <div class="form-check">
                            <input class="form-check-input city-filter" type="checkbox" 
                                value="${city.id}" id="city${city.id}"
                                ${activeFilters.cities.includes(city.id) ? 'checked' : ''}>
                            <label class="form-check-label" for="city${city.id}">
                                ${city.name}
                            </label>
                        </div>`;
                    });
                }
                cityFilterContainer.innerHTML = html;
                
                // Add event listeners to new city checkboxes
                document.querySelectorAll('.city-filter').forEach(checkbox => {
                    checkbox.addEventListener('change', handleCityFilter);
                });
            });
        }

        // Handle stream filter changes
        function handleStreamFilter() {
            activeFilters.streams = Array.from(document.querySelectorAll('.stream-filter:checked'))
                .map(cb => parseInt(cb.value));
            loadCourses();
            loadUniversities();
        }

        // Handle course filter changes
        function handleCourseFilter() {
            activeFilters.courses = Array.from(document.querySelectorAll('.course-filter:checked'))
                .map(cb => parseInt(cb.value));
            loadUniversities();
        }

        // Handle state filter changes
        function handleStateFilter() {
            activeFilters.states = Array.from(document.querySelectorAll('.state-filter:checked'))
                .map(cb => parseInt(cb.value));
            loadCities();
            loadUniversities();
        }

        // Handle city filter changes
        function handleCityFilter() {
            activeFilters.cities = Array.from(document.querySelectorAll('.city-filter:checked'))
                .map(cb => parseInt(cb.value));
            loadUniversities();
        }

        // Handle affiliation filter changes
        function handleAffiliationFilter() {
            activeFilters.affiliations = Array.from(document.querySelectorAll('.affiliation-filter:checked'))
                .map(cb => parseInt(cb.value));
            loadUniversities();
        }

        // Handle category filter changes
        function handleCategoryFilter() {
            activeFilters.categories = Array.from(document.querySelectorAll('.category-filter:checked'))
                .map(cb => cb.value);
            loadUniversities();
        }

        // Load universities with current filters and sorting
        function loadUniversities() {
            fetch('get_universities.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    filters: activeFilters,
                    sort: currentSort,
                    direction: sortDirection
                })
            })
            .then(response => response.json())
            .then(data => {
                let html = '';
                
                if (data.universities.length === 0) {
                    html = `<tr><td colspan="9" class="text-center py-4">No universities found matching your criteria</td></tr>`;
                } else {
                    data.universities.forEach(uni => {
                        // Format streams and courses
                        const streams = uni.streams ? uni.streams.split(',') : [];
                        const courses = uni.courses ? uni.courses.split(',') : [];
                        
                        html += `
                        <tr class="university-row">
                            <td>${uni.rank || '-'}</td>
                            <td class="logo-cell">
                                ${uni.logo ? `<img src="${uni.logo}" alt="${uni.name}" class="img-fluid" style="max-height:40px">` : '-'}
                            </td>
                            <td>${uni.name}</td>
                            <td>${uni.affiliation || '-'}</td>
                            <td>${uni.category || '-'}</td>
                            <td>${uni.state || '-'}</td>
                            <td>${uni.city || '-'}</td>
                            <td>
                                ${streams.length > 0 ? 
                                    streams.map(s => `<span class="badge bg-primary me-1">${s}</span>`).join('') : 
                                    '-'}
                            </td>
                            <td>
                                ${courses.length > 0 ? 
                                    courses.map(c => `<span class="badge bg-info course-badge me-1" 
                                        data-university="${uni.id}" data-course="${c.split('|')[0]}">${c.split('|')[1]}</span>`).join('') : 
                                    '-'}
                            </td>
                        </tr>
                        <tr class="course-details" id="details-${uni.id}">
                            <td colspan="9">
                                <div class="accordion" id="courseAccordion-${uni.id}"></div>
                            </td>
                        </tr>`;
                    });
                }
                
                universityTable.innerHTML = html;
                resultCount.textContent = `Showing ${data.universities.length} of ${data.total} universities`;
                
                // Add event listeners to course badges
                document.querySelectorAll('.course-badge').forEach(badge => {
                    badge.addEventListener('click', function() {
                        const universityId = this.dataset.university;
                        const courseId = this.dataset.course;
                        const courseName = this.textContent;
                        
                        loadCourseDetails(universityId, courseId, courseName);
                    });
                });
            });
        }

        // Load course details for a specific university and course
        function loadCourseDetails(universityId, courseId, courseName) {
            fetch('get_course_details.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ universityId, courseId })
            })
            .then(response => response.json())
            .then(details => {
                const accordionId = `courseAccordion-${universityId}`;
                const accordionContainer = document.getElementById(accordionId);
                
                if (!accordionContainer) return;
                
                let html = '';
                details.forEach((detail, index) => {
                    const accordionItemId = `course-${universityId}-${courseId}-${index}`;
                    
                    // Format program types
                    const programTypes = detail.program_types ? 
                        detail.program_types.split(',').map(p => `<span class="badge bg-secondary me-1">${p}</span>`) : 
                        '-';
                    
                    // Format entrance exams
                    const entranceExams = detail.entrance_exams ? 
                        detail.entrance_exams.split(',').map(e => `<span class="badge bg-warning text-dark me-1">${e}</span>`) : 
                        '-';
                    
                    html += `
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-${accordionItemId}">
                            <button class="accordion-button ${index > 0 ? 'collapsed' : ''}" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#collapse-${accordionItemId}" 
                                aria-expanded="${index === 0 ? 'true' : 'false'}" 
                                aria-controls="collapse-${accordionItemId}">
                                ${courseName} - ${detail.duration}
                            </button>
                        </h2>
                        <div id="collapse-${accordionItemId}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" 
                            aria-labelledby="heading-${accordionItemId}" data-bs-parent="#${accordionId}">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Duration:</strong> ${detail.duration}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Avg Fee/Year:</strong> ${detail.avg_fee_per_year ? '₹' + detail.avg_fee_per_year : '-'}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Program Types:</strong> ${programTypes}
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <strong>Entrance Exams:</strong> ${entranceExams}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                });
                
                accordionContainer.innerHTML = html;
                
                // Show the course details row
                document.querySelectorAll('.course-details').forEach(row => {
                    row.classList.remove('active');
                });
                document.getElementById(`details-${universityId}`).classList.add('active');
            });
        }

        // Reset all filters
        document.getElementById('resetFilters').addEventListener('click', function() {
            // Uncheck all filter checkboxes
            document.querySelectorAll('.stream-filter, .course-filter, .state-filter, .city-filter, .affiliation-filter, .category-filter')
                .forEach(cb => cb.checked = false);
            
            // Reset filter state
            activeFilters = {
                streams: [],
                courses: [],
                states: [],
                cities: [],
                affiliations: [],
                categories: []
            };
            
            // Reset sorting
            currentSort = 'rank';
            sortDirection = 'ASC';
            document.querySelectorAll('[data-sort]').forEach(header => {
                header.classList.remove('sort-asc', 'sort-desc');
            });
            document.querySelector('[data-sort="rank"]').classList.add('sort-asc');
            
            loadCourses();
            loadCities();
            loadUniversities();
        });

        // Initialize event listeners
        document.querySelectorAll('.stream-filter').forEach(checkbox => {
            checkbox.addEventListener('change', handleStreamFilter);
        });
        
        document.querySelectorAll('.state-filter').forEach(checkbox => {
            checkbox.addEventListener('change', handleStateFilter);
        });
        
        document.querySelectorAll('.affiliation-filter').forEach(checkbox => {
            checkbox.addEventListener('change', handleAffiliationFilter);
        });
        
        document.querySelectorAll('.category-filter').forEach(checkbox => {
            checkbox.addEventListener('change', handleCategoryFilter);
        });

        // Initial load
        loadCourses();
        loadCities();
        loadUniversities();
    });
    </script>
</body>
</html>