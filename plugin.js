function hello(name) {
  alert("Hello " + name);
}
$(document).ready(function () {
  let currentPage = 0;
  let data = [];
  let filteredData = [];
  let tableData = [];
  let numPages = 0;
  let itemsPerPage = 10;
  let researchUnits = [];
  let organisation_id = "35c17f17-6b5f-4385-aa8b-6b1d33a10157"; // not in used yet

  // Function to display the table with data
  function displayTable(data, page) {
    let start = page * itemsPerPage;
    let end = start + itemsPerPage;
    tableData = filteredData.slice(start, end);

    let tbody = $("#software-table tbody");
    tbody.empty();
    for (let i = 0; i < tableData.length; i++) {
      let row = `
          <tr>
            <td> ${tableData[i].brand_name} </td>
            <td> ${tableData[i].slug} </td>
            <td> ${tableData[i].short_statement} </td>
          </tr>
          `;
      tbody.append(row);
    }
  }

  function fetchResearchUnits(url) {
    $.ajax({
      type: "GET",
      url,
      success: function (response) {
        researchUnits = response;
        console.log("🎹 researchUnits", researchUnits);
        // displayResearchUnits(researchUnits);
      },
    });
  }

  fetchResearchUnits(
    "https://research-software-directory.org/api/v1/rpc/list_child_organisations?parent_id=35c17f17-6b5f-4385-aa8b-6b1d33a10157&organisation_id=not.eq.35c17f17-6b5f-4385-aa8b-6b1d33a10157"
  );

  // Display a list of pages
  function displayPages() {
    let pages = $("#pages");
    pages.empty();
    for (let i = 0; i < numPages; i++) {
      let page = `
          <button class="page" data-page="${i}"> ${i + 1} </button>
          `;
      pages.append(page);
    }
  }
  // Function to paginate the table
  function paginate() {
    $("#page-number").text(currentPage + 1);
    $("#results").text(filteredData.length);
    displayTable(data, currentPage);
    displayPages();
  }

  // params = { organisationId ='35c17f17-6b5f-4385-aa8b-6b1d33a10157' }

  function fetchSoftwareByOrganisation(url) {
    // `https://research-software-directory.org/api/v1/rpc/list_child_organisations?parent_id=${organisationId}&organisation_id=not.eq.${organisationId}`
    // Fetch data from the API
    console.log("🎹 response", url);
    $.ajax({
      type: "GET",
      // url: "https://research-software-directory.org/api/v1/software?select=*,organisation!left(name)&organisation.id=eq.35c17f17-6b5f-4385-aa8b-6b1d33a10157&limit=10&offset=2",
      url,
      success: function (response) {
        data = response;
        filteredData = response;
        $("#count").text(data.length);
        $("#results").text(data.length);
        // console.log('🎹 data', data);
        numPages = Math.ceil(data.length / itemsPerPage);
        paginate();
      },
    });
  }
  // Fetch data for first time
  fetchSoftwareByOrganisation(
    "https://research-software-directory.org/api/v1/software?select=*,organisation!left(name)&organisation.id=eq.35c17f17-6b5f-4385-aa8b-6b1d33a10157"
  );

  // Filter by research unit

  function fetchListOfResesarchUnits() {
    $.getJSON(
      "https://research-software-directory.org/api/v1/rpc/list_child_organisations?parent_id=35c17f17-6b5f-4385-aa8b-6b1d33a10157&organisation_id=not.eq.35c17f17-6b5f-4385-aa8b-6b1d33a10157",
      (data) => {
        console.log("🎹 data", data);

        $.each(data, (index, item) => {
          $("#research-units").append(
            `
            <button organisation-id='${item.organisation_id}'>
              ${item.organisation_id}
            </button>
            `
          );
          // $("#research-units").append("
          // <button onclick="hello(\"" + item.organization_id + "\")'>"
          //     ${item.organisation_id}
          //     </button>
          //     ");
        });
      }
    );
  }
  fetchListOfResesarchUnits();

  $("#reset-filters").click(function () {
    fetchSoftwareByOrganisation(
      "https://research-software-directory.org/api/v1/software?select=*,organisation!left(name)&organisation.id=eq.35c17f17-6b5f-4385-aa8b-6b1d33a10157"
    );
  });

  $("#research-units").on("click", "button", function () {
    var organisationId = $(this).attr("organisation-id");
    fetchSoftwareByOrganisation(
      `https://research-software-directory.org/api/v1/software?select=*,organisation!inner(name)&organisation.id=eq.${organisationId}`
    );
  });

  // Handle searches
  // Search by brnad name, slug or description or short statement
  $("#search").keyup(function () {
    let searchTerm = $(this).val().toLowerCase();
    filteredData = data.filter((software) => {
      return (
        software.brand_name?.toLowerCase().indexOf(searchTerm) !== -1 ||
        software.short_statement?.toLowerCase().indexOf(searchTerm) !== -1
        // software.slug?.toLowerCase().indexOf(searchTerm) !== -1 ||
        // software.description?.toLowerCase().indexOf(searchTerm) !== -1
      );
    });
    numPages = Math.ceil(filteredData.length / itemsPerPage);
    currentPage = 0;
    paginate();
  });

  // Handle clicks on the "Previous" button
  $("#previous").click(function () {
    if (currentPage > 0) {
      currentPage--;
      paginate();
    }
  });

  // Handle clicks on the "Next" button
  $("#next").click(function () {
    if (currentPage < numPages - 1) {
      currentPage++;
      paginate();
    }
  });

  // handle clicks on the page buttons
  $("#pages").on("click", ".page", function () {
    currentPage = $(this).data("page");
    paginate();
  });
});
