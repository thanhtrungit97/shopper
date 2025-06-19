<?php

declare(strict_types=1);

namespace Shopper\Events;

use Shopper\Feature;
use Shopper\Sidebar\AbstractAdminSidebar;
use Shopper\Sidebar\Contracts\Builder\Group;
use Shopper\Sidebar\Contracts\Builder\Item;
use Shopper\Sidebar\Contracts\Builder\Menu;

final class ReportSidebar extends AbstractAdminSidebar
{
    public function extendWith(Menu $menu): Menu
    {
        $menu->group(__('Báo cáo'), function (Group $group): void {
            $group->weight(3);
            $group->setAuthorized();
            $group->setGroupItemsClass('space-y-1');
            $group->setHeadingClass('sh-heading');

            if (Feature::enabled('report')) {
                $group->item(__('Tổng kết cuối ngày'), function (Item $item): void {
                    $item->weight(2);
                    // $item->setAuthorized($this->user->hasPermissionTo('browse_report'));
                    $item->setItemClass('sh-sidebar-item group');
                    $item->setActiveClass('sh-sidebar-item-active');
                    $item->setInactiveClass('sh-sidebar-item-inactive');
                    $item->route('shopper.report.daily-report');
                    $item->useSpa();
                    $item->setIcon(
                        icon: 'untitledui-pie-chart-02',
                        iconClass: 'size-5 ' . ($item->isActive() ? 'text-primary-600' : 'text-gray-400 dark:text-gray-500'),
                        attributes: [
                            'stroke-width' => '1.5',
                        ],
                    );
                });
                $group->item(__('Báo cáo bán hàng'), function (Item $item): void {
                    $item->weight(2);
                    // $item->setAuthorized($this->user->hasPermissionTo('browse_report'));
                    $item->setItemClass('sh-sidebar-item group');
                    $item->setActiveClass('sh-sidebar-item-active');
                    $item->setInactiveClass('sh-sidebar-item-inactive');
                    $item->route('shopper.report.sale-report');
                    $item->useSpa();
                    $item->setIcon(
                        icon: 'untitledui-file-03',
                        iconClass: 'size-5 ' . ($item->isActive() ? 'text-primary-600' : 'text-gray-400 dark:text-gray-500'),
                        attributes: [
                            'stroke-width' => '1.5',
                        ],
                    );
                });
            }
        });

        return $menu;
    }
}
