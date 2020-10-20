import React from 'react';

const ExchangeInput = (props) => {
    const {
        currency,
        defaultValue,
    } = props;

    return (
        <label>
            {currency.toUpperCase()}:
            <input
                type="number"
                name={"exchange[" + currency + "]"}
                min="0.001"
                step="0.001"
                defaultValue={defaultValue}
            />
        </label>
    );
}

export default ExchangeInput;
