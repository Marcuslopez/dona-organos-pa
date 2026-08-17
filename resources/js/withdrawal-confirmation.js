const withdrawalCheckbox = document.getElementById('confirmWithdrawal');
const withdrawalButton = document.getElementById('confirmWithdrawalButton');

if (withdrawalCheckbox && withdrawalButton) {
    const updateWithdrawalButton = () => {
        withdrawalButton.disabled = !withdrawalCheckbox.checked;
    };

    withdrawalCheckbox.addEventListener('change', updateWithdrawalButton);
    updateWithdrawalButton();
}
