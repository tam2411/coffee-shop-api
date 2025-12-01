<h2>Xin chào {{ $user->full_name }}! 🎉</h2>

<p>Bạn là <strong>TOP người mua hàng nhiều nhất tháng</strong> của Coffee Shop.</p>

<p>🎁 <strong>Voucher của bạn:</strong></p>

<ul>
    <li><strong>Mã giảm giá:</strong> {{ $voucher->voucher_code }}</li>
    <li><strong>Giảm:</strong> {{ $voucher->discount_percent }}%</li>
    <li><strong>Miễn phí ship:</strong> {{ $voucher->free_shipping ? 'Có' : 'Không' }}</li>
</ul>

<p>Hãy sử dụng mã này khi thanh toán để nhận ưu đãi nhé! ☕️</p>

<p>Trân trọng,<br>Coffee Shop Team</p>
