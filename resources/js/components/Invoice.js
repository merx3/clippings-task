import axios from 'axios';
import React, { Component } from 'react';
import ReactDOM from 'react-dom';

import ExchangeInput from "./InvoiceForm/ExchangeInput";
import OutputCurrencyInput from "./InvoiceForm/OutputCurrencyInput";

class Invoice extends Component {

    constructor(props) {
        super(props);
        this.state = {
            selectedFile: null,
            exchangeRates: {
                eur: 1.0,
                usd: 0.987,
                gbp: 0.878
            },
            result: ''
        };
        this.handleSubmit = this.handleSubmit.bind(this);
    }

    setSelectedFile(e) {
        this.setState({ selectedFile: e.target.files[0] });
    }

    getDefaultExchangeRate() {
        const rates = this.state.exchangeRates;
        return Object.keys(rates).find(currency => rates[currency] === 1.0);
    }

    handleSubmit(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append(
            "invoice_file",
            this.state.selectedFile,
            this.state.selectedFile.name
        );

        axios.post("api/invoice", formData)
            .then((res) => {
                this.setState({ result: res.data.sum });
            })
            .catch((err) => alert("Invoice Sum Calculation Error: "  + err));
    }

    render() {
        return (
            <div className="container">
                <div className="row justify-content-center">
                    <div className="col-md-12">
                        <div className="card">
                            <div className="card-header"><h3>Upload Invoice Report</h3></div>
                            <div className="card-body">
                            <form onSubmit={this.handleSubmit}>
                                <label>
                                    CSV File:
                                    <input
                                        type="file"
                                        name="invoice_file"
                                        onChange={(e) => this.setSelectedFile(e)}
                                        required
                                    />
                                </label>
                                Exchange Rates: <br/>
                                {Object.keys(this.state.exchangeRates).map((currency) =>
                                    <ExchangeInput
                                        key={currency}
                                        currency={currency}
                                        defaultValue={this.state.exchangeRates[currency]}
                                    />
                                )}

                                <OutputCurrencyInput
                                    currencies={Object.keys(this.state.exchangeRates)}
                                    defaultValue={this.getDefaultExchangeRate()}
                                />

                                <label>
                                    Customer filter (optional):
                                    <input
                                        type="text"
                                        name="filter_customer"
                                    />
                                </label>
                                <button className="btn btn-primary" type="submit">Calculate</button>
                                {this.state.result && <span className="result">Result: {this.state.result}</span>}
                            </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

export default Invoice;

if (document.getElementById('invoice')) {
    ReactDOM.render(<Invoice />, document.getElementById('invoice'));
}
