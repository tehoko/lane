<?php
/**
* Sets new column position (used for swapping columns).
*
* @param user-name    name of list owner
* @param list-id      id of list
* @param column-id    id of column to change position of
* @param new-position integer specifying desired column position
*/
class SetColumnPositionJob extends GenericListJob
{
	public function execute()
	{
		$list = $this->getList();

		$newPos = intval($this->getArgument('new-position'));
		if ($newPos < 0 or $newPos >= count($list->content->columns))
			throw new SimpleException('Invalid column target position: ' . $newPos . '.');

		$oldPos = ListService::getColumnPos($list, $this->getArgument('column-id'));

		self::swap($list->content->columns, $oldPos, $newPos);

		foreach ($list->content->rows as $i => $row)
			self::swap($list->content->rows[$i]->content, $oldPos, $newPos);

		ListService::saveOrUpdate($list);
	}

	private static function swap(&$subject, $oldPos, $newPos)
	{
		$entity = $subject[$oldPos];
		array_splice($subject, $oldPos, 1);
		array_splice($subject, $newPos, 0, [$entity]);
	}
}
