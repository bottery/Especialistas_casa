// Sistema de Exportación de Datos (CSV y PDF)
class DataExporter {
    constructor() {
        this.pdfLoaded = false;
    }

    async ensurePDFLibrary() {
        if (this.pdfLoaded) return;
        
        return new Promise((resolve, reject) => {
            if (typeof jsPDF !== 'undefined') {
                this.pdfLoaded = true;
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
            script.onload = () => {
                this.pdfLoaded = true;
                resolve();
            };
            script.onerror = () => reject(new Error('No se pudo cargar la biblioteca PDF'));
            document.head.appendChild(script);
        });
    }

    // Exportar a CSV
    exportToCSV(data, filename = 'export.csv', columns = null) {
        if (!data || data.length === 0) {
            alert('No hay datos para exportar');
            return;
        }

        // Si no se especifican columnas, usar todas las claves del primer objeto
        const headers = columns || Object.keys(data[0]);
        
        // Crear CSV header
        let csv = headers.map(h => this.escapeCSV(h)).join(',') + '\n';
        
        // Agregar filas
        data.forEach(row => {
            const values = headers.map(header => {
                const value = row[header] !== undefined ? row[header] : '';
                return this.escapeCSV(String(value));
            });
            csv += values.join(',') + '\n';
        });

        // Descargar
        this.downloadFile(csv, filename, 'text/csv;charset=utf-8;');
    }

    escapeCSV(value) {
        if (value === null || value === undefined) return '';
        value = String(value);
        
        // Si contiene comas, comillas o saltos de línea, envolver en comillas
        if (value.includes(',') || value.includes('"') || value.includes('\n')) {
            value = '"' + value.replace(/"/g, '""') + '"';
        }
        
        return value;
    }

    // Exportar a PDF
    async exportToPDF(data, filename = 'export.pdf', options = {}) {
        try {
            await this.ensurePDFLibrary();
            
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF(options.orientation || 'portrait');

            // Configuración
            const title = options.title || 'Reporte';
            const columns = options.columns || Object.keys(data[0] || {});
            const pageWidth = doc.internal.pageSize.getWidth();
            const pageHeight = doc.internal.pageSize.getHeight();
            
            // Título
            doc.setFontSize(18);
            doc.text(title, pageWidth / 2, 20, { align: 'center' });
            
            // Fecha
            doc.setFontSize(10);
            doc.text(`Fecha: ${new Date().toLocaleDateString('es-ES')}`, 14, 30);
            
            // Tabla
            let startY = 40;
            const rowHeight = 10;
            const colWidth = (pageWidth - 28) / columns.length;
            
            // Headers
            doc.setFillColor(79, 70, 229); // Indigo
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(11);
            doc.rect(14, startY, pageWidth - 28, rowHeight, 'F');
            
            columns.forEach((col, i) => {
                doc.text(String(col), 14 + (i * colWidth) + 2, startY + 7);
            });
            
            // Filas
            doc.setTextColor(0, 0, 0);
            doc.setFontSize(10);
            startY += rowHeight;
            
            data.forEach((row, rowIndex) => {
                // Nueva página si es necesario
                if (startY + rowHeight > pageHeight - 20) {
                    doc.addPage();
                    startY = 20;
                }
                
                // Alternar color de fila
                if (rowIndex % 2 === 0) {
                    doc.setFillColor(249, 250, 251); // Gray-50
                    doc.rect(14, startY, pageWidth - 28, rowHeight, 'F');
                }
                
                columns.forEach((col, i) => {
                    const value = row[col] !== undefined ? String(row[col]) : '';
                    const text = value.length > 30 ? value.substring(0, 27) + '...' : value;
                    doc.text(text, 14 + (i * colWidth) + 2, startY + 7);
                });
                
                startY += rowHeight;
            });
            
            // Footer
            const totalPages = doc.internal.getNumberOfPages();
            for (let i = 1; i <= totalPages; i++) {
                doc.setPage(i);
                doc.setFontSize(8);
                doc.setTextColor(128);
                doc.text(
                    `Página ${i} de ${totalPages}`,
                    pageWidth / 2,
                    pageHeight - 10,
                    { align: 'center' }
                );
            }
            
            // Descargar
            doc.save(filename);
            
        } catch (error) {
            console.error('Error al generar PDF:', error);
            alert('Error al generar el PDF: ' + error.message);
        }
    }

    // Exportar tabla HTML directamente
    exportTableToCSV(tableSelector, filename = 'tabla.csv') {
        const table = document.querySelector(tableSelector);
        if (!table) {
            alert('No se encontró la tabla');
            return;
        }

        let csv = '';
        
        // Headers
        const headers = table.querySelectorAll('thead th');
        csv += Array.from(headers).map(th => this.escapeCSV(th.textContent.trim())).join(',') + '\n';
        
        // Rows
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const values = Array.from(cells).map(td => this.escapeCSV(td.textContent.trim()));
            csv += values.join(',') + '\n';
        });
        
        this.downloadFile(csv, filename, 'text/csv;charset=utf-8;');
    }

    // Utilidad para descargar archivo
    downloadFile(content, filename, mimeType) {
        const blob = new Blob([content], { type: mimeType });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    // Crear botones de exportación
    createExportButtons(containerSelector, data, options = {}) {
        const container = document.querySelector(containerSelector);
        if (!container) return;

        const filename = options.filename || 'export';
        const title = options.title || 'Reporte';
        const columns = options.columns || (data.length > 0 ? Object.keys(data[0]) : []);

        const buttonsHTML = `
            <div class="export-buttons flex gap-2">
                <button onclick="window.dataExporter.exportToCSV(window.exportData, '${filename}.csv', ${JSON.stringify(columns)})" 
                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Exportar CSV
                </button>
                <button onclick="window.dataExporter.exportToPDF(window.exportData, '${filename}.pdf', {title: '${title}', columns: ${JSON.stringify(columns)}})" 
                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Exportar PDF
                </button>
            </div>
        `;

        container.innerHTML = buttonsHTML;
        window.exportData = data; // Guardar data globalmente para los botones
    }
}

// Instancia global
window.dataExporter = new DataExporter();
