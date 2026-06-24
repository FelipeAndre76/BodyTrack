<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\MealLog;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserFoodController extends Controller
{
    public function index()
    {
        $foods = Food::with('latestScanLog')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('foods.mine', compact('foods'));
    }

    public function update(Request $request, Food $food)
    {
        $this->authorizeOwnedFood($food);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'calories_per_100g' => 'required|numeric|min:0',
            'protein_per_100g' => 'required|numeric|min:0',
            'carbs_per_100g' => 'required|numeric|min:0',
            'fat_per_100g' => 'required|numeric|min:0',
        ]);

        $food->update($validated);

        $this->notifyUser(
            'Alimento atualizado',
            "{$food->name} foi atualizado com sucesso.",
            'bi-pencil-square',
            route('foods.mine')
        );

        return redirect()->route('foods.mine')
            ->with('success', 'Alimento atualizado com sucesso!');
    }

    public function destroy(Food $food)
    {
        $this->authorizeOwnedFood($food);

        $isUsed = MealLog::where('food_id', $food->id)->exists();

        if ($isUsed) {
            return redirect()->route('foods.mine')
                ->withErrors(['food' => 'Esse alimento já foi usado em refeições e não pode ser excluído para preservar seu histórico.']);
        }

        $foodName = $food->name;
        $food->delete();

        $this->notifyUser(
            'Alimento excluído',
            "{$foodName} foi removido da sua lista.",
            'bi-trash',
            route('foods.mine')
        );

        return redirect()->route('foods.mine')
            ->with('success', 'Alimento excluído com sucesso!');
    }

    private function authorizeOwnedFood(Food $food): void
    {
        if ((int) $food->user_id !== (int) Auth::id()) {
            abort(403);
        }
    }

    private function notifyUser(string $title, string $message, string $icon, ?string $linkUrl = null): void
    {
        UserNotification::create([
            'user_id' => Auth::id(),
            'title' => $title,
            'message' => $message,
            'type' => 'success',
            'icon' => $icon,
            'link_url' => $linkUrl,
        ]);
    }
}
