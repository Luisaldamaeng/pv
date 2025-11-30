/**
 * @jest-environment jsdom
 */

const fs = require('fs');
const path = require('path');

// Mock Bootstrap's Modal
global.bootstrap = {
    Modal: class {
        constructor(element) {
            this.element = element;
        }
        show() { /* mock show */ }
        hide() { /* mock hide */ }
        static getInstance(element) {
            return new bootstrap.Modal(element);
        }
    }
};

describe('Product Management Page (productos.php)', () => {
    let document;
    let window;

    beforeEach(() => {
        // Load the HTML structure from productos.php into the JSDOM environment
        const html = fs.readFileSync(path.resolve(__dirname, './productos.php'), 'utf8');

        // JSDOM will parse the HTML and create the DOM.
        // We need to extract the script content to execute it in the test environment.
        const scriptContent = html.substring(html.indexOf('<script>') + 8, html.lastIndexOf('</script>'));

        // Set up the document body
        document.body.innerHTML = html;

        // Mock fetch before executing the script
        global.fetch = jest.fn(() =>
            Promise.resolve({
                json: () => Promise.resolve({
                    status: 'success',
                    data: `<tr>
                             <td class="puntero-celda"></td>
                             <td>PROD001</td>
                             <td>Test Product</td>
                             <td>150.00</td>
                             <td>123456789</td>
                             <td><input type="checkbox" class="selecc-checkbox" data-id="1"></td>
                             <td>100.00</td>
                             <td>12</td>
                             <td>1001</td>
                           </tr>`,
                    totalFiltro: 1,
                    totalRegistros: 100,
                    paginacion: '<button>1</button>'
                }),
            })
        );

        // Execute the script from the PHP file
        // This will attach all the event listeners.
        eval(scriptContent);
    });

    test('getData should fetch and display data on initial load', async () => {
        // The script runs `getData()` on DOMContentLoaded, which we simulate in beforeEach.
        // We need to wait for the fetch mock to resolve and the DOM to be updated.
        await new Promise(process.nextTick);

        const contentBody = document.getElementById('content');
        const totalLabel = document.getElementById('lbl-total');

        // Check if fetch was called
        expect(fetch).toHaveBeenCalledWith('load.php', expect.any(Object));

        // Check if the table content was updated
        expect(contentBody.innerHTML).toContain('Test Product');
        expect(contentBody.querySelector('tr').cells[1].textContent).toBe('PROD001');

        // Check if the total label is correct
        expect(totalLabel.textContent).toBe('Mostrando 1 de 100 registros');
    });

    test('clicking "Limpiar" button should clear search fields and reload data', async () => {
        // Setup: give some values to the search fields
        document.getElementById('busqueda_codigoprod').value = '123';
        document.getElementById('campo').value = 'some text';

        // Action: click the clear button
        document.getElementById('btn-limpiar').click();

        // Wait for async operations
        await new Promise(process.nextTick);

        // Assertions
        expect(document.getElementById('busqueda_codigoprod').value).toBe('');
        expect(document.getElementById('campo').value).toBe('');

        // It should call fetch again to reload the data
        // It was called once on load, and a second time on clear.
        expect(fetch).toHaveBeenCalledTimes(2);
    });

    test('clicking a sortable column header should change sort order and reload data', async () => {
        // The first call to getData is in beforeEach
        fetch.mockClear();

        const nombreHeader = document.querySelector('thead th.sort:nth-child(3)'); // "Nombre" column
        const orderColInput = document.getElementById('orderCol');
        const orderTypeInput = document.getElementById('orderType');

        // Initial state from HTML is asc on col 2
        orderColInput.value = '2';
        orderTypeInput.value = 'asc';
        nombreHeader.classList.add('asc');

        // Action: click to sort descending
        nombreHeader.click();
        await new Promise(process.nextTick);

        // Assertions
        expect(orderColInput.value).toBe('2'); // cellIndex of "Nombre" is 2
        expect(orderTypeInput.value).toBe('desc');
        expect(nombreHeader.classList.contains('desc')).toBe(true);
        expect(nombreHeader.classList.contains('asc')).toBe(false);
        expect(fetch).toHaveBeenCalledTimes(1);

        // Action: click again to sort ascending
        nombreHeader.click();
        await new Promise(process.nextTick);

        // Assertions
        expect(orderTypeInput.value).toBe('asc');
        expect(nombreHeader.classList.contains('asc')).toBe(true);
        expect(nombreHeader.classList.contains('desc')).toBe(false);
        expect(fetch).toHaveBeenCalledTimes(2);
    });

    test('submitting the edit form should send an update request', async () => {
        fetch.mockClear();

        // Mock a successful update response
        fetch.mockResolvedValueOnce(Promise.resolve({
            json: () => Promise.resolve({ status: 'success', message: 'Producto actualizado' }),
        }));

        // Setup: populate the edit modal form
        const form = document.getElementById('form-edita');
        form.querySelector('#id').value = '1';
        form.querySelector('#nombre').value = 'Updated Name';

        // Action: submit the form
        form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
        await new Promise(process.nextTick);

        // Assertions
        expect(fetch).toHaveBeenCalledWith('actualizar.php', expect.any(Object));

        // Check the FormData content
        const fetchOptions = fetch.mock.calls[0][1];
        const formData = fetchOptions.body;
        expect(formData.get('id')).toBe('1');
        expect(formData.get('nombre')).toBe('Updated Name');

        // After a successful update, it should call getData() again
        // The second call is getData()
        expect(fetch).toHaveBeenCalledWith('load.php', expect.any(Object));
        expect(fetch).toHaveBeenCalledTimes(2);
    });
});