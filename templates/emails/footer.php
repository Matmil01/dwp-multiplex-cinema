            </td>
        </tr>
        <tr>
            <td style="padding: 16px 24px; border-top: 1px solid #ddd; color: #999; font-size: 12px;">
                <?= htmlspecialchars($company['street'] . ' ' . $company['house_number'] . ', ' . $company['postal_code'] . ' ' . $company['city'] . ', ' . $company['country'], ENT_QUOTES) ?> &middot; <?= htmlspecialchars($company['phone'], ENT_QUOTES) ?><br>
                &copy; <?= date('Y') ?> Untitled Cinema
            </td>
        </tr>
    </table>
</body>
</html>
