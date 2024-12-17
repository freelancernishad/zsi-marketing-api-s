<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', Arial, sans-serif;
        }

        body {
            background: #f4f7fc;
            padding: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Invoice Container */
        .invoice-container {
            background: #fff;
            width: 900px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        /* Header Section */
        .invoice-header {
            background: #4a90e2;
            color: #fff;
            padding: 15px 30px;
        }

        .header-table {
            width: 100%;
        }

        .header-table td {
            vertical-align: middle;
        }

        .header-table .company-info {
            text-align: left;
            padding-right: 20px;
        }

        .header-table .company-info h1 {
            font-size: 20px;
            font-weight: bold;
        }

        .header-table .company-info p {
            font-size: 12px;
            opacity: 0.9;
        }

        .header-table .invoice-logo {
            text-align: right;
        }

        .header-table .invoice-logo h2 {
            font-size: 18px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .header-table .invoice-logo p {
            font-size: 12px;
        }

        /* Invoice Details Section */
        .invoice-details {
            padding: 20px 30px;
            border-bottom: 1px solid #e6ebf1;
        }

        .details-table {
            width: 100%;
            border-spacing: 0 10px;
        }

        .details-table td {
            font-size: 14px;
            color: #555;
            padding: 8px;
            vertical-align: top;
        }

        .details-table td h3 {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            border-bottom: 2px solid #4a90e2;
            display: inline-block;
        }

        .details-table td:first-child {
            padding-right: 20px;
            text-align: left;
        }

        .details-table td:last-child {
            text-align: right;
        }

        /* Features Section */
        .features {
            padding: 20px 30px;
            font-size: 14px;
        }

        .features h3 {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #4a90e2;
            display: inline-block;
        }

        .features ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .features ul li {
            color: #555;
            padding: 8px 0;
            border-bottom: 1px solid #e6ebf1;
            display: flex;
            align-items: center;
        }

        .features ul li:last-child {
            border-bottom: none;
        }

        .features ul li::before {
            content: '✔';
            color: #4a90e2;
            font-size: 16px;
            font-weight: bold;
            margin-right: 8px;
        }

        /* Footer Section */
        .invoice-footer {
            background: #f8f9fa;
            padding: 15px 30px;
            text-align: center;
            font-size: 12px;
            border-top: 1px solid #e6ebf1;
        }

        .invoice-footer p {
            color: #777;
        }

        .invoice-footer p:first-child {
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <header class="invoice-header">
            <table class="header-table">
                <tr>
                    <td class="company-info">
                        <h1>Zsi.ai</h1>
                        <p>Professional Business Solutions</p>
                    </td>
                    <td class="invoice-logo">
                        <h2>Invoice</h2>
                        <p>#23</p>
                    </td>
                </tr>
            </table>
        </header>

        <section class="invoice-details">
            <table class="details-table">
                <tr>
                    <td>
                        <h3>Business Details</h3>
                        <p><strong>Business Name:</strong> Zsi.ai</p>
                        <p><strong>Date:</strong> 2024-12-16</p>
                        <p><strong>Start Date:</strong> 2024-12-16</p>
                        <p><strong>End Date:</strong> 2025-03-26</p>
                    </td>
                    <td>
                        <h3>Package Details</h3>
                        <p><strong>Package:</strong> Social Media Standard Package</p>
                        <p><strong>Price:</strong> $500.00</p>
                        <p><strong>Discount:</strong> 0%</p>
                    </td>
                </tr>
            </table>
        </section>

        <section class="features">
            <h3>Included Features</h3>
            <ul>
                <li>Comprehensive SEO audit</li>
                <li>Keyword research and optimization</li>
                <li>On-page and off-page SEO</li>
                <li>Monthly blog post (10 articles)</li>
                <li>Dedicated SEO Expert</li>
                <li>Social media setup (Facebook, LinkedIn, Instagram, Twitter)</li>
                <li>25 posts per month (across platforms)</li>
                <li>Engagement (responding to comments/messages)</li>
                <li>Social media ads setup and monitoring</li>
                <li>Dedicated social media manager</li>
                <li>Monthly performance report</li>
                <li>Backlinks report</li>
                <li>Ads campaign report and new plan</li>
            </ul>
        </section>

        <footer class="invoice-footer">
            <p>Generated on: 2024-12-16 07:20:19</p>
            <p>&copy; 2024 Zsi.ai. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
