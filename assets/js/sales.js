document.addEventListener("DOMContentLoaded", () => {
    const chartContainer = document.getElementById("morris-line-example");
    const yearSelector = document.getElementById("yearSelector");
    const monthButtons = document.querySelectorAll(".month-btn");

    let currentYear = new Date().getFullYear();
    let currentMonth = new Date().getMonth() + 1;

    // Populate year selector from 2022 to current year + 5
    for (let year = 2025; year <= currentYear; year++) {
        const option = document.createElement("option");
        option.value = year;
        option.textContent = year;
        if (year === currentYear) {
            option.selected = true;
        }
        yearSelector.appendChild(option);
    }

    let chart = null;

    function loadChartData(month, year) {
        fetch(`api/manage_sales.php?month=${month}&year=${year}`)
            .then((response) => response.json())
            .then((data) => {
                // Fill missing dates with 0 if not present
                const daysInMonth = new Date(year, month, 0).getDate();
                const completeData = [];

                for (let day = 1; day <= daysInMonth; day++) {
                    const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    const entry = data.find(d => d.date === dateStr);
                    completeData.push({
                        date: dateStr,
                        amount: entry ? parseFloat(entry.amount) : 0
                    });
                }

                if (chart) {
                    chart.setData(completeData);
                } else {
                    chart = new Morris.Line({
                        element: 'morris-line-example',
                        data: completeData,
                        xkey: 'date',
                        ykeys: ['amount'],
                        labels: ['Total Sales'],
                        parseTime: false,
                        resize: true,
                        lineColors: ['#4a81d4'],
                        gridTextColor: '#555'
                    });
                }
            })
            .catch((error) => {
                console.error("Error loading chart data:", error);
            });
    }

    // Initial chart load
    loadChartData(currentMonth, currentYear);

    // Handle month button click
    monthButtons.forEach((btn) => {
        btn.addEventListener("click", () => {
            const selectedMonth = parseInt(btn.getAttribute("data-month"));
            currentMonth = selectedMonth;

            // Set active class
            monthButtons.forEach((b) => b.classList.remove("active"));
            btn.classList.add("active");

            loadChartData(currentMonth, currentYear);
        });
    });

    // Handle year selector change
    yearSelector.addEventListener("change", () => {
        currentYear = parseInt(yearSelector.value);
        loadChartData(currentMonth, currentYear);
    });
    // Generate Report Button Logic
    const generateBtn = document.getElementById("generateReportBtn");
    generateBtn.addEventListener("click", () => {
        const url = `api/sales_report.php?month=${currentMonth}&year=${currentYear}`;
        window.open(url, "_blank"); // open in new tab
    });

});
