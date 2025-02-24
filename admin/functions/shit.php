function generateMonthlyReport() {
    const { jsPDF } = window.jspdf;
    let doc = new jsPDF();
    let selectedMonth = document.getElementById("reportMonth")?.value;

    if (!selectedMonth) {
        alert("Please select a month.");
        return;
    }

    fetch(`../functions/fetch-appointment-by-month.php?month=${selectedMonth}`)
        .then(response => response.json())
        .then(data => {
            if (!data.appointments || data.appointments.length === 0) {
                alert("No appointments found for this month.");
                return;
            }

            addReportHeader(doc, `Monthly Appointment Report - ${selectedMonth}`);

            let tableData = [
                ["Date", "Code", "Faculty", "Student", "Start", "End", "Agenda", "Status"]
            ];

            data.appointments.forEach(appointment => {
                tableData.push([
                    appointment.date_logged,
                    appointment.appointment_code || "N/A",
                    `${appointment.faculty_fname || ""} ${appointment.faculty_lname || ""}`.trim(),
                    `${appointment.stud_fname || ""} ${appointment.stud_lname || ""}`.trim(),
                    formatTime(appointment.start_time),
                    formatTime(appointment.end_time),
                    appointment.agenda || "N/A",
                    appointment.status || "N/A"
                ]);
            });

            doc.autoTable({
                startY: 60,
                head: [tableData[0]],
                body: tableData.slice(1),
                theme: "grid",
                styles: { fontSize: 9 },
                margin: { bottom: 20 }
            });

            let y = doc.lastAutoTable.finalY + 10;

            // **Status Summary**
            let statusY = y;
            doc.setFont("helvetica", "bold");
            doc.text("Status Summary", 15, statusY);
            doc.setFont("helvetica", "normal");
            let statusCounts = data.statusCounts;
            y = statusY + 5;
            Object.keys(statusCounts).forEach(status => {
                doc.text(`${status} - ${statusCounts[status]}`, 20, y);
                y += 5;
            });

            // **Agenda Summary**
            let agendaY = y + 10;
            doc.setFont("helvetica", "bold");
            doc.text("Agenda Summary", 15, agendaY);
            doc.setFont("helvetica", "normal");
            let agendaCounts = data.agendaCounts;
            y = agendaY + 5;
            Object.keys(agendaCounts).forEach(agenda => {
                doc.text(`${agenda} - ${agendaCounts[agenda]}`, 20, y);
                y += 5;
            });

            addPageNumbers(doc);
            doc.save(`Monthly_Appointment_Report_${selectedMonth}.pdf`);
        })
        .catch(error => {
            console.error("Error fetching data:", error);
            alert("Error generating monthly report.");
        });
}
