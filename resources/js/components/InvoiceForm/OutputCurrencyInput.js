import React from 'react';

const OutputCurrencyInput = (props) => {
    const {
        currencies,
        defaultValue
    } = props;

    return (
        <label>
            Output currency:
            <select name="output_currency"
                    defaultValue={defaultValue}>
                {currencies.map((currency) =>
                    <option key={currency}
                            value={currency}>
                        {currency.toUpperCase()}
                    </option>
                )}
            </select>
        </label>
    );
}

export default OutputCurrencyInput;
